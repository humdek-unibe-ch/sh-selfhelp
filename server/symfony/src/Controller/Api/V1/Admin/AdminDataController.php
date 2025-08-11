<?php

namespace App\Controller\Api\V1\Admin;

use App\Controller\Trait\RequestValidatorTrait;
use App\Exception\ServiceException;
use App\Service\CMS\DataService;
use App\Service\CMS\DataTableService;
use App\Service\Core\ApiResponseFormatter;
use App\Service\JSON\JsonSchemaValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminDataController extends AbstractController
{
    use RequestValidatorTrait;

    public function __construct(
        private readonly DataService $dataService,
        private readonly DataTableService $dataTableService,
        private readonly ApiResponseFormatter $responseFormatter,
        private readonly JsonSchemaValidationService $jsonSchemaValidationService
    ) {
    }

    /**
     * Get all data tables
     */
    public function getDataTables(): JsonResponse
    {
        try {
            $tables = $this->dataTableService->getFormDataTables();
            $result = array_map(static function ($table) {
                return [
                    'id' => $table->getId(),
                    'name' => $table->getName(),
                    'displayName' => $table->getDisplayName(),
                    'created' => $table->getTimestamp()?->format(DATE_ATOM),
                ];
            }, $tables);

            return $this->responseFormatter->formatSuccess(['dataTables' => $result]);
        } catch (\Throwable $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get data rows from a data table
     * Supported query params: table_name (string, required), user_id (int|null), exclude_deleted (bool, default true)
     * Other legacy parameters are fixed: filter = '', own_entries_only = false, db_first = false
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $tableName = (string)$request->query->get('table_name', '');
            if ($tableName === '') {
                return $this->responseFormatter->formatError('Missing or invalid table_name', Response::HTTP_BAD_REQUEST);
            }

            $dataTable = $this->dataService->getDataTableByName($tableName);
            if (!$dataTable) {
                return $this->responseFormatter->formatError('Data table not found', Response::HTTP_NOT_FOUND);
            }

            $userId = $request->query->has('user_id') ? (int)$request->query->get('user_id') : null;
            $excludeDeleted = filter_var($request->query->get('exclude_deleted', 'true'), FILTER_VALIDATE_BOOLEAN);

            // filter = '', ownEntriesOnly = false, dbFirst = false
            $rows = $this->dataService->getData($dataTable->getId(), '', false, $userId, false, $excludeDeleted);

            return $this->responseFormatter->formatSuccess(['rows' => $rows]);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Throwable $e) {
            return $this->responseFormatter->formatError(
                'Failed to fetch data',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete a single record from any data table (soft-delete via trigger type)
     */
    public function deleteRecord(Request $request, int $recordId): JsonResponse
    {
        try {
            $ownEntriesOnly = filter_var($request->query->get('own_entries_only', 'true'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $ownEntriesOnly = $ownEntriesOnly === null ? true : $ownEntriesOnly;

            $success = $this->dataService->deleteData($recordId, $ownEntriesOnly);
            return $this->responseFormatter->formatSuccess(['deleted' => $success]);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Throwable $e) {
            return $this->responseFormatter->formatError(
                'Failed to delete record',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete an entire data table and all associated rows/columns/cells
     */
    public function deleteDataTable(string $tableName): JsonResponse
    {
        try {
            $deleted = $this->dataTableService->deleteDataTable($tableName);
            if (!$deleted) {
                return $this->responseFormatter->formatError('Data table not found', Response::HTTP_NOT_FOUND);
            }
            return $this->responseFormatter->formatSuccess(['deleted' => true]);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Throwable $e) {
            return $this->responseFormatter->formatError(
                'Failed to delete data table',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Delete selected columns from a data table
     * Expects JSON body: { "columns": ["colA", "colB", ...] }
     */
    public function deleteColumns(Request $request, string $tableName): JsonResponse
    {
        try {
            $data = $this->validateRequest($request, 'requests/admin/delete_data_columns', $this->jsonSchemaValidationService);
            $columns = $data['columns'] ?? [];

            $result = $this->dataTableService->deleteColumns($tableName, $columns);
            if ($result === false) {
                return $this->responseFormatter->formatError('Data table not found', Response::HTTP_NOT_FOUND);
            }
            return $this->responseFormatter->formatSuccess(['deleted_column_count' => $result]);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Throwable $e) {
            return $this->responseFormatter->formatError(
                'Failed to delete columns',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get columns for a given data table
     */
    public function getColumns(string $tableName): JsonResponse
    {
        try {
            $columns = $this->dataTableService->getColumns($tableName);
            if ($columns === false) {
                return $this->responseFormatter->formatError('Data table not found', Response::HTTP_NOT_FOUND);
            }
            return $this->responseFormatter->formatSuccess(['columns' => $columns]);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Throwable $e) {
            return $this->responseFormatter->formatError(
                'Failed to fetch columns',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get column names for a given data table
     */
    public function getColumnNames(string $tableName): JsonResponse
    {
        try {
            $columnNames = $this->dataTableService->getColumnsNames($tableName);
            if ($columnNames === false) {
                return $this->responseFormatter->formatError('Data table not found', Response::HTTP_NOT_FOUND);
            }
            return $this->responseFormatter->formatSuccess(['columnNames' => $columnNames]);
        } catch (ServiceException $e) {
            return $this->responseFormatter->formatError(
                $e->getMessage(),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Throwable $e) {
            return $this->responseFormatter->formatError(
                'Failed to fetch column names',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}


