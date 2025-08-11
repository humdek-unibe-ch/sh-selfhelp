<?php

namespace App\Service\CMS;

use App\Entity\DataTable;
use App\Entity\Section;
use App\Exception\ServiceException;
use App\Service\CMS\Common\StyleNames;
use App\Repository\DataTableRepository;
use App\Service\Core\TransactionService;
use App\Service\Core\UserContextAwareService;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for managing dataTables creation and column management
 */
class DataTableService extends UserContextAwareService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionService $transactionService,
        private readonly DataTableRepository $dataTableRepository,
        ACLService $aclService,
        UserContextService $userContextService,
        PageRepository $pageRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct($userContextService, $aclService, $pageRepository, $sectionRepository);
    }

    /**
     * Create dataTable for form section if it's a form type
     * 
     * @param Section $section The section to check and create dataTable for
     * @return DataTable|null The created dataTable or null if not a form section
     * @throws ServiceException
     */
    public function createDataTableForFormSection(Section $section): ?DataTable
    {
        $formName = $section->getId();


        // Check if dataTable already exists
        $existingDataTable = $this->dataTableRepository->findOneBy(['name' => $formName]);
        if ($existingDataTable) {
            return $existingDataTable;
        }

        $this->entityManager->beginTransaction();
        
        try {
            // Create new dataTable
            $dataTable = new DataTable();
            $dataTable->setName($formName);
            $dataTable->setTimestamp(new \DateTime());
            
            // Set displayName from section's displayName field if available
            $displayName = $this->getDisplayNameFromSection($section);
            if ($displayName) {
                $dataTable->setDisplayName($displayName);
            }

            $this->entityManager->persist($dataTable);
            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                'insert',
                'transactionBy_by_system',
                'dataTables',
                $dataTable->getId()
            );

            $this->entityManager->commit();
            
            return $dataTable;
            
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw new ServiceException(
                'Failed to create dataTable for form section: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous' => $e, 'sectionId' => $section->getId()]
            );
        }
    }

    /**
     * Update dataTable displayName when section field is updated
     * 
     * @param Section $section The form section
     * @param string $newDisplayName The new display name
     * @return bool Success status
     * @throws ServiceException
     */
    public function updateDataTableDisplayName(Section $section, string $newDisplayName): bool
    {

        $formName = $section->getId();
        if (!$formName) {
            return false;
        }

        $dataTable = $this->dataTableRepository->findOneBy(['name' => $formName]);
        if (!$dataTable) {
            // Create dataTable for form section if it doesn't exist
            $this->createDataTableForFormSection($section);
            $dataTable = $this->dataTableRepository->findOneBy(['name' => $formName]);
        }

        $this->entityManager->beginTransaction();
        
        try {
            $dataTable->setDisplayName($newDisplayName);
            
            // Log transaction
            $this->transactionService->logTransaction(
                'update',
                'transactionBy_by_system',
                'dataTables',
                $dataTable->getId()
            );

            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return true;
            
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw new ServiceException(
                'Failed to update dataTable displayName: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous' => $e, 'sectionId' => $section->getId()]
            );
        }
    }

    /**
     * Generate unique form name for new form sections
     * 
     * @param string $baseName Base name for the form
     * @return string Unique form name
     */
    public function generateUniqueFormName(string $baseName = 'form'): string
    {
        $timestamp = time();
        $formName = $baseName . '_' . $timestamp;
        
        // Ensure uniqueness
        $counter = 1;
        while ($this->dataTableRepository->findOneBy(['name' => $formName])) {
            $formName = $baseName . '_' . $timestamp . '_' . $counter;
            $counter++;
        }
        
        return $formName;
    }

    /**
     * Check if a section is a form section
     * 
     * @param Section $section The section to check
     * @return bool True if it's a form section
     */
    public function isFormSection(Section $section): bool
    {
        $style = $section->getStyle();
        if (!$style) {
            return false;
        }
        
        return in_array($style->getName(), StyleNames::FORM_STYLE_NAMES);
    }    

    /**
     * Get display name from section's "displayName" field
     * 
     * @param Section $section The section
     * @return string|null The display name
     */
    private function getDisplayNameFromSection(Section $section): ?string
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('sft')
           ->from('App\Entity\SectionsFieldsTranslation', 'sft')
           ->join('sft.field', 'f')
           ->where('sft.section = :section')
           ->andWhere('f.name = :fieldName')
           ->setParameter('section', $section)
           ->setParameter('fieldName', 'displayName')
           ->setMaxResults(1);
        
        $translation = $qb->getQuery()->getOneOrNullResult();
        
        return $translation ? $translation->getContent() : null;
    }

    /**
     * Get all form dataTables
     * 
     * @return DataTable[] Array of dataTables that correspond to forms
     */
    public function getFormDataTables(): array
    {
        // For now, return all dataTables
        // In the future, we could add a flag or naming convention to identify form dataTables
        return $this->dataTableRepository->findAll();
    }

    /**
     * Delete dataTable and all associated data
     * 
     * @param string $tableName The name of the table to delete
     * @return bool Success status
     * @throws ServiceException
     */
    public function deleteDataTable(string $tableName): bool
    {
        $dataTable = $this->dataTableRepository->findOneBy(['name' => $tableName]);
        if (!$dataTable) {
            return false;
        }

        $this->entityManager->beginTransaction();
        
        try {
            // Log transaction before deletion
            $this->transactionService->logTransaction(
                'delete',
                'transactionBy_by_system',
                'dataTables',
                $dataTable->getId()
            );

            // Doctrine will cascade delete all related rows, columns, and cells
            $this->entityManager->remove($dataTable);
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return true;
            
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw new ServiceException(
                'Failed to delete dataTable: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous' => $e, 'tableName' => $tableName]
            );
        }
    }

    /**
     * Check if dataTable exists by name
     * 
     * @param string $tableName The table name
     * @return bool True if exists
     */
    public function dataTableExists(string $tableName): bool
    {
        return $this->dataTableRepository->findOneBy(['name' => $tableName]) !== null;
    }

    /**
     * Get dataTable statistics
     * 
     * @param string $tableName The table name
     * @return array Statistics array
     */
    public function getDataTableStats(string $tableName): array
    {
        $dataTable = $this->dataTableRepository->findOneBy(['name' => $tableName]);
        if (!$dataTable) {
            return [];
        }

        $qb = $this->entityManager->createQueryBuilder();
        
        // Count total rows
        $totalRows = $qb->select('COUNT(dr.id)')
            ->from('App\Entity\DataRow', 'dr')
            ->where('dr.dataTable = :dataTable')
            ->setParameter('dataTable', $dataTable)
            ->getQuery()
            ->getSingleScalarResult();

        // Count columns
        $totalColumns = count($dataTable->getDataCols());

        return [
            'tableName' => $tableName,
            'displayName' => $dataTable->getDisplayName(),
            'totalRows' => $totalRows,
            'totalColumns' => $totalColumns,
            'created' => $dataTable->getTimestamp()
        ];
    }

    /**
     * Delete selected columns from a data table
     * Returns number of deleted columns, false if table not found
     */
    public function deleteColumns(string $tableName, array $columnNames): int|false
    {
        $dataTable = $this->dataTableRepository->findOneBy(['name' => $tableName]);
        if (!$dataTable) {
            return false;
        }

        if (count($columnNames) === 0) {
            return 0;
        }

        $this->entityManager->beginTransaction();

        try {
            $deletedCount = 0;

            // Fetch columns by names
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('dc')
                ->from('App\\Entity\\DataCol', 'dc')
                ->where('dc.dataTable = :dataTable')
                ->andWhere($qb->expr()->in('dc.name', ':names'))
                ->setParameter('dataTable', $dataTable)
                ->setParameter('names', $columnNames);

            $columns = $qb->getQuery()->getResult();

            foreach ($columns as $column) {
                $this->entityManager->remove($column);
                $deletedCount++;
            }

            if ($deletedCount > 0) {
                $this->transactionService->logTransaction(
                    'delete',
                    'transactionBy_by_system',
                    'dataTables',
                    $dataTable->getId()
                );
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            return $deletedCount;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw new ServiceException(
                'Failed to delete columns: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous' => $e, 'tableName' => $tableName]
            );
        }
    }

    /**
     * Get columns for a data table by name
     * Returns an array of column definitions [{ id, name }] or false if not found
     */
    public function getColumns(string $tableName): array|false
    {
        $dataTable = $this->dataTableRepository->findOneBy(['name' => $tableName]);
        if (!$dataTable) {
            return false;
        }

        $columns = $dataTable->getDataCols();
        $result = [];
        foreach ($columns as $col) {
            $result[] = [
                'id' => $col->getId(),
                'name' => $col->getName(),
            ];
        }
        return $result;
    }

     /**
     * Get columns for a data table by name
     * Returns an array of column names or false if not found
     */
    public function getColumnsNames(string $tableName): array|false
    {
        $dataTable = $this->dataTableRepository->findOneBy(['name' => $tableName]);
        if (!$dataTable) {
            return false;
        }

        $columns = $dataTable->getDataCols();
        $result = ['record_id', 'entry_date', 'user_code', 'id_users', 'user_name', 'triggerType'];
        foreach ($columns as $col) {
            $result[] = $col->getName();
        }
        return $result;
    }
}
