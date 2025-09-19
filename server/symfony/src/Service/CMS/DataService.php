<?php

namespace App\Service\CMS;

use App\Entity\DataTable;
use App\Entity\DataRow;
use App\Entity\DataCol;
use App\Entity\DataCell;
use App\Entity\Lookup;
use App\Exception\ServiceException;
use App\Repository\DataTableRepository;
use App\Service\Core\TransactionService;
use App\Service\Core\BaseService;
use App\Service\Core\LookupService;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Cache\Core\CacheService;
use App\Service\Core\UserContextAwareService;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Service\CMS\FormFileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Core service for handling form data operations with transactions and validation
 * ENTITY RULE - Uses association objects instead of primitive foreign keys
 */
class DataService extends BaseService
{


    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionService $transactionService,
        private readonly DataTableRepository $dataTableRepository,
        private readonly LookupService $lookupService,
        private readonly ACLService $aclService,
        private readonly UserContextService $userContextService,
        private readonly UserContextAwareService $userContextAwareService,
        private readonly CacheService $cache,
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly FormFileUploadService $formFileUploadService
    ) {
    }

    /**
     * Save form data to database with proper transaction handling
     * 
     * @param string $tableName The name of the data table
     * @param array $data The form data to save
     * @param string $transactionBy Who initiated the transaction
     * @param array|null $updateBasedOn Optional fields to update existing record
     * @param bool $ownEntriesOnly Whether to restrict updates to user's own entries
     * @return int|false The record ID on success or false on failure
     * @throws ServiceException
     */
    public function saveData(
        string $tableName,
        array $data,
        string $transactionBy = LookupService::TRANSACTION_BY_BY_USER,
        ?array $updateBasedOn = null,
        bool $ownEntriesOnly = true
    ): int|false {
        $this->entityManager->beginTransaction();

        try {
            // Ensure user ID is set
            if (!isset($data['id_users'])) {
                $currentUser = $this->userContextAwareService->getCurrentUser();
                $data['id_users'] = $currentUser ? $currentUser->getId() : 1; // Guest user fallback
            }

            // Get or create data table
            $dataTable = $this->getOrCreateDataTable($tableName);

            // Check for existing record to update
            if ($updateBasedOn !== null) {
                $filter = '';
                foreach ($updateBasedOn as $key => $value) {
                    $filter = $filter . ' AND ' . $key . ' = "' . $value . '"';
                }
                $existingRecord = $this->getData($dataTable->getId(), $filter, $ownEntriesOnly, $currentUser->getId(), true);
                if ($existingRecord) {
                    $recordId = $this->updateExistingRecord($existingRecord['record_id'], $data, $transactionBy);
                    $this->entityManager->commit();

                    // Invalidate data table cache after updating record
                    $this->cache
                        ->withCategory(CacheService::CATEGORY_DATA_TABLES)
                        ->invalidateAllListsInCategory();

                    $this->cache
                        ->withCategory(CacheService::CATEGORY_DATA_TABLES)
                        ->invalidateEntityScope(CacheService::ENTITY_SCOPE_DATA_TABLE, $dataTable->getId());

                    $this->cache
                        ->withCategory(CacheService::CATEGORY_DATA_TABLES)
                        ->invalidateEntityScope(CacheService::ENTITY_SCOPE_USER, $currentUser->getId());

                    return $recordId;
                } elseif (count($updateBasedOn) > 0) {
                    // Trying to update non-existent record
                    $this->entityManager->rollback();
                    return false;
                }
            }

            // Create new record
            $recordId = $this->createNewRecord($dataTable, $data, $transactionBy);

            $this->entityManager->commit();

            // Invalidate data table cache after creating new record
            $this->cache
                ->withCategory(CacheService::CATEGORY_DATA_TABLES)
                ->invalidateAllListsInCategory();

            return $recordId;

        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw new ServiceException(
                'Failed to save form data: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous' => $e, 'tableName' => $tableName]
            );
        }
    }

    /**
     * Delete form data record
     * 
     * @param int $recordId The ID of the record to delete
     * @param bool $ownEntriesOnly Whether to restrict to user's own entries
     * @return bool Success status
     * @throws ServiceException
     */
    public function deleteData(int $recordId, bool $ownEntriesOnly = true): bool
    {
        $this->entityManager->beginTransaction();

        try {
            $dataRow = $this->entityManager->getRepository(DataRow::class)->find($recordId);
            if (!$dataRow) {
                $this->throwNotFound('Record not found');
            }

            // Check ownership if required
            if ($ownEntriesOnly) {
                $currentUser = $this->userContextService->getCurrentUser();
                if (!$currentUser || $dataRow->getIdUsers() !== $currentUser->getId()) {
                    $this->throwForbidden('Access denied to this record');
                }
            }

            // Extract file data before marking as deleted
            $fileData = $this->extractFileDataFromRecord($dataRow);

            // Mark as deleted instead of physical deletion
            $deletedTriggerType = $this->lookupService->getLookupIdByValue('actionTriggerTypes', LookupService::ACTION_TRIGGER_TYPES_DELETED);
            $dataRow->setIdActionTriggerTypes($deletedTriggerType);

            // Log transaction
            $this->transactionService->logTransaction(
                'delete',
                LookupService::TRANSACTION_BY_BY_USER,
                'dataTables',
                $dataRow->getDataTable()?->getId()
            );

            $this->entityManager->flush();
            $this->entityManager->commit();

            // Clean up associated files after successful database deletion
            if (!empty($fileData)) {
                try {
                    $this->formFileUploadService->deleteFiles($fileData);
                } catch (\Exception $e) {
                    // Log file cleanup error but don't fail the deletion
                    error_log("File cleanup failed for record {$recordId}: " . $e->getMessage());
                }
            }

            // Invalidate data table cache after deleting record
            $this->cache
                ->withCategory(CacheService::CATEGORY_DATA_TABLES)
                ->invalidateAllListsInCategory();

            $this->cache
                ->withCategory(CacheService::CATEGORY_DATA_TABLES)
                ->invalidateEntityScope(CacheService::ENTITY_SCOPE_DATA_TABLE, $dataRow->getDataTable()->getId());

            return true;

        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw new ServiceException(
                'Failed to delete record: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous' => $e, 'recordId' => $recordId]
            );
        }
    }

    /**
     * Extract file data from a data record
     *
     * @param DataRow $dataRow The data row to extract files from
     * @return array Array of file paths keyed by field name
     */
    private function extractFileDataFromRecord(DataRow $dataRow): array
    {
        $fileData = [];

        // Get all data cells for this row
        $dataCells = $this->entityManager->getRepository(DataCell::class)->findBy([
            'dataRow' => $dataRow
        ]);

        foreach ($dataCells as $dataCell) {
            $fieldName = $dataCell->getDataCol()->getName();
            $fieldValue = $dataCell->getValue();

            // Check if this field contains file information
            if ($this->formFileUploadService->isFileInputField($fieldName, $dataRow->getDataTable()->getId())) {
                try {
                    // Try to decode as JSON (for multiple files)
                    $decoded = json_decode($fieldValue, true);
                    if (is_array($decoded)) {
                        // Multiple files
                        $fileData[$fieldName] = array_filter($decoded, function ($path) {
                            return is_string($path) && str_contains($path, 'uploads/form-files/');
                        });
                    } elseif (is_string($fieldValue) && str_contains($fieldValue, 'uploads/form-files/')) {
                        // Single file
                        $fileData[$fieldName] = $fieldValue;
                    }
                } catch (\Exception $e) {
                    // If JSON parsing fails, check if it's a direct file path
                    if (is_string($fieldValue) && str_contains($fieldValue, 'uploads/form-files/')) {
                        $fileData[$fieldName] = $fieldValue;
                    }
                }
            }
        }

        return $fileData;
    }

    /**
     * Get or create a data table by name
     * 
     * @param string $tableName The name of the table
     * @return DataTable
     */
    private function getOrCreateDataTable(string $tableName): DataTable
    {
        $dataTable = $this->dataTableRepository->findOneBy(['name' => $tableName]);

        if (!$dataTable) {
            $dataTable = new DataTable();
            $dataTable->setName($tableName);
            $dataTable->setTimestamp(new \DateTime());

            $this->entityManager->persist($dataTable);
            $this->entityManager->flush(); // Flush to get the ID
        }

        return $dataTable;
    }

    /**
     * Update existing record
     * 
     * @param int $recordId The ID of the record to update
     * @param array $data New data
     * @param string $transactionBy Transaction initiator
     * @return int Record ID
     */
    private function updateExistingRecord(int $recordId, array $data, string $transactionBy): int
    {

        $dataRow = $this->entityManager->getRepository(DataRow::class)->find($recordId);
        if (!$dataRow) {
            $this->throwNotFound('Record not found');
        }

        // Update timestamp and trigger type
        $dataRow->setTimestamp(new \DateTime());
        $dataRow->setIdUsers($data['id_users']);

        $triggerTypeId = $this->getTriggerTypeId($data);
        $dataRow->setIdActionTriggerTypes($triggerTypeId);

        // Remove id_users from data as it's already set on the row
        unset($data['id_users']);

        // Get or create columns and update cells
        $columns = $this->getOrCreateColumns($dataRow->getDataTable(), $data);

        foreach ($data as $fieldName => $fieldValue) {
            $column = $columns[$fieldName];

            // Find existing cell or create new one
            $dataCell = $this->entityManager->getRepository(DataCell::class)
                ->findOneBy(['dataRow' => $dataRow, 'dataCol' => $column]);

            if (!$dataCell) {
                $dataCell = new DataCell();
                $dataCell->setDataRow($dataRow);
                $dataCell->setDataCol($column);
                $this->entityManager->persist($dataCell);
            }

            $dataCell->setValue($fieldValue ?? '');
        }

        // Log transaction
        $this->transactionService->logTransaction(
            'update',
            $transactionBy,
            'dataTables',
            $dataRow->getDataTable()?->getId()
        );

        $this->entityManager->flush();

        return $dataRow->getId();
    }

    /**
     * Create new record
     * 
     * @param DataTable $dataTable The data table
     * @param array $data Form data
     * @param string $transactionBy Transaction initiator
     * @return int Record ID
     */
    private function createNewRecord(DataTable $dataTable, array $data, string $transactionBy): int
    {
        // Create data row
        $dataRow = new DataRow();
        $dataRow->setDataTable($dataTable);
        $dataRow->setTimestamp(new \DateTime());
        $dataRow->setIdUsers($data['id_users']);

        $triggerTypeId = $this->getTriggerTypeId($data);
        $dataRow->setIdActionTriggerTypes($triggerTypeId);

        $this->entityManager->persist($dataRow);
        $this->entityManager->flush(); // Flush to get the ID

        // Remove id_users from data as it's already set on the row
        unset($data['id_users']);

        // Get or create columns
        $columns = $this->getOrCreateColumns($dataTable, $data);

        // Create data cells
        foreach ($data as $fieldName => $fieldValue) {
            $column = $columns[$fieldName];

            $dataCell = new DataCell();
            $dataCell->setDataRow($dataRow);
            $dataCell->setDataCol($column);
            //if field value is emty array, set it to []
            if (is_array($fieldValue) && empty($fieldValue)) {
                $fieldValue = '[]';
            }
            $dataCell->setValue($fieldValue ?? '');

            $this->entityManager->persist($dataCell);
        }

        // Log transaction
        $this->transactionService->logTransaction(
            'insert',
            $transactionBy,
            'dataTables',
            $dataTable->getId()
        );

        $this->entityManager->flush();

        return $dataRow->getId();
    }

    /**
     * Get or create columns for data table
     * 
     * @param DataTable $dataTable The data table
     * @param array $data Form data to extract column names
     * @return array<string, DataCol> Array of column name => DataCol
     */
    private function getOrCreateColumns(DataTable $dataTable, array $data): array
    {
        $columns = [];

        foreach (array_keys($data) as $fieldName) {
            $column = $this->entityManager->getRepository(DataCol::class)
                ->findOneBy(['dataTable' => $dataTable, 'name' => $fieldName]);

            if (!$column) {
                $column = new DataCol();
                $column->setDataTable($dataTable);
                $column->setName($fieldName);
                $this->entityManager->persist($column);
                $this->entityManager->flush(); // Flush to get the ID
            }

            $columns[$fieldName] = $column;
        }

        return $columns;
    }

    /**
     * Get trigger type ID from form data
     * 
     * @param array $data Form data
     * @return int Trigger type ID
     */
    private function getTriggerTypeId(array $data): int
    {
        $triggerType = $data['trigger_type'] ?? LookupService::ACTION_TRIGGER_TYPES_FINISHED;

        $validTriggerTypes = [
            LookupService::ACTION_TRIGGER_TYPES_STARTED,
            LookupService::ACTION_TRIGGER_TYPES_UPDATED,
            LookupService::ACTION_TRIGGER_TYPES_DELETED,
            LookupService::ACTION_TRIGGER_TYPES_FINISHED
        ];

        if (!in_array($triggerType, $validTriggerTypes)) {
            $triggerType = LookupService::ACTION_TRIGGER_TYPES_FINISHED;
        }

        return $this->lookupService->getLookupIdByValue('actionTriggerTypes', $triggerType);
    }

    /**
     * Get data table by name
     * 
     * @param string $tableName Table name
     * @return DataTable|null
     */
    public function getDataTableByName(string $tableName): ?DataTable
    {
        return $this->dataTableRepository->findOneBy(['name' => $tableName]);
    }

    /**
     * Get data table by display name
     * 
     * @param string $displayName Display name
     * @return DataTable|null
     */
    public function getDataTableByDisplayName(string $displayName): ?DataTable
    {
        return $this->dataTableRepository->findOneBy(['displayName' => $displayName]);
    }

    /**
     * Get the last record of a data table
     * 
     * @param string $dataTableName Data table name
     * @return array
     */
    public function getFormRecordData(string $dataTableName): array
    {
        $dataTableId = $this->dataTableRepository->findOneBy(['name' => $dataTableName])->getId();
        $data = $this->getData($dataTableId, 'ORDER BY record_id DESC LIMIT 1', true, $this->userContextService->getCurrentUser()->getId(), false, true);
        return $data;
    }

    /**
     * Fetch data records from a data table using the legacy stored procedure behavior.
     * Mirrors the old get_data($dataTableId, $filter, $own_entries_only, $user_id, $db_first, $exclude_deleted) logic.
     *
     * - If the filter contains dynamic placeholders ("{{"), it will be ignored
     * - When ownEntriesOnly is true and userId is not provided, current user is used (or -1 if not available)
     * - When ownEntriesOnly is false and userId is not provided, -1 is used to fetch all users
     * - When dbFirst is true, the first row (or empty array) is returned
     */
    public function getData(
        int $dataTableId,
        string $filter = '',
        bool $ownEntriesOnly = true,
        ?int $userId = null,
        bool $dbFirst = false,
        bool $excludeDeleted = true
    ): array {
        try {
            // Guard: ignore malformed dynamic filter attempts
            if (str_contains($filter, '{{')) {
                $filter = '';
            }

            // Resolve user id as per legacy rules
            $resolvedUserId = $userId;
            if ($resolvedUserId === null) {
                if ($ownEntriesOnly) {
                    $currentUser = $this->userContextService->getCurrentUser();
                    $resolvedUserId = $currentUser ? $currentUser->getId() : -1;
                } else {
                    $resolvedUserId = -1; // all users
                }
            }

            $rows = $this->dataTableRepository->getDataTableWithFilter(
                $dataTableId,
                $resolvedUserId,
                $filter,
                $excludeDeleted
            );

            if ($dbFirst) {
                return isset($rows[0]) ? $rows[0] : [];
            }

            return $rows;
        } catch (\Throwable $e) {
            throw new ServiceException(
                'Failed to fetch data: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous' => $e, 'dataTableId' => $dataTableId]
            );
        }
    }


}
