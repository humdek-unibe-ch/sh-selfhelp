<?php

namespace App\Service\CMS\Admin;

use App\Entity\Action;
use App\Entity\Lookup;
use App\Repository\ActionRepository;
use App\Repository\LookupRepository;
use App\Service\Core\BaseService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Service\Core\CacheableServiceTrait;
use App\Service\Core\GlobalCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Exception\ServiceException;

class AdminActionService extends BaseService
{
    
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionService $transactionService,
        private readonly ActionRepository $actionRepository,
        private readonly LookupRepository $lookupRepository
    ) {
    }

    /**
     * Get actions with pagination
     */
    public function getActions(int $page = 1, int $pageSize = 20, ?string $search = null, ?string $sort = null, string $sortDirection = 'asc'): array
    {
        // Create cache key based on parameters
        $cacheKey = "actions_list_{$page}_{$pageSize}_" . md5(($search ?? '') . ($sort ?? '') . $sortDirection);
        
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_ACTIONS,
            $cacheKey,
            function() use ($page, $pageSize, $search, $sort, $sortDirection) {
                return $this->actionRepository->findActionsWithPagination($page, $pageSize, $search, $sort, $sortDirection);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_ACTIONS)
        );
    }

    /**
     * Get a single action by ID
     */
    public function getActionById(int $actionId): array
    {
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_ACTIONS,
            "action_{$actionId}",
            function() use ($actionId) {
                /** @var Action|null $action */
                $action = $this->entityManager->find(Action::class, $actionId);
                if (!$action instanceof Action) {
                    throw new ServiceException('Action not found', Response::HTTP_NOT_FOUND);
                }
                return $this->formatAction($action);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_ACTIONS)
        );
    }

    /**
     * Update an action's basic fields and config
     */
    public function updateAction(int $actionId, array $data): array
    {
        $this->entityManager->beginTransaction();
        try {
            /** @var Action|null $action */
            $action = $this->entityManager->find(Action::class, $actionId);
            if (!$action) {
                throw new ServiceException('Action not found', Response::HTTP_NOT_FOUND);
            }

            $originalAction = clone $action;

            if (array_key_exists('name', $data)) {
                $action->setName((string) $data['name']);
            }

            if (array_key_exists('id_actionTriggerTypes', $data)) {
                $lookup = $this->lookupRepository->find($data['id_actionTriggerTypes']);
                if (!$lookup instanceof Lookup) {
                    throw new ServiceException('Invalid action trigger type', Response::HTTP_BAD_REQUEST);
                }
                $action->setActionTriggerType($lookup);
            }

            if (array_key_exists('config', $data)) {
                // Store as JSON text (validated at controller level against schema)
                $action->setConfig(is_string($data['config']) ? $data['config'] : json_encode($data['config'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            if (array_key_exists('id_dataTables', $data)) {
                $dataTable = $this->entityManager->getReference(\App\Entity\DataTable::class, (int)$data['id_dataTables']);
                if (!$dataTable) {
                    throw new ServiceException('Invalid data table', Response::HTTP_BAD_REQUEST);
                }
                $action->setDataTable($dataTable);
            } else {
                throw new ServiceException('Field "id_dataTables" is required', Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'actions',
                $action->getId(),
                (object) ['old_action' => $originalAction, 'new_action' => $action],
                'Action updated: ' . $action->getName() . ' (ID: ' . $action->getId() . ')'
            );

            $this->entityManager->commit();

            return $this->formatAction($action);
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to update action: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Create a new action
     */
    public function createAction(array $data): array
    {
        $this->entityManager->beginTransaction();
        try {
            $action = new Action();

            if (empty($data['name'])) {
                $this->throwBadRequest('Field "name" is required');
            }
            $action->setName((string) $data['name']);

            if (!empty($data['id_actionTriggerTypes'])) {
                $lookup = $this->lookupRepository->find($data['id_actionTriggerTypes']);
                if (!$lookup instanceof Lookup) {
                    $this->throwBadRequest('Invalid action trigger type');
                }
                $action->setActionTriggerType($lookup);
            } else {
                $this->throwBadRequest('Field "id_actionTriggerTypes" is required');
            }

            if (array_key_exists('config', $data)) {
                $action->setConfig(is_string($data['config']) ? $data['config'] : json_encode($data['config'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            if (array_key_exists('id_dataTables', $data)) {
                $dataTable = $this->entityManager->getReference(\App\Entity\DataTable::class, (int)$data['id_dataTables']);
                if (!$dataTable) {
                    $this->throwBadRequest('Invalid data table');
                }
                $action->setDataTable($dataTable);
            } else {
                $this->throwBadRequest('Field "id_dataTables" is required');
            }

            $this->entityManager->persist($action);
            $this->entityManager->flush();

            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_INSERT,
                LookupService::TRANSACTION_BY_BY_USER,
                'actions',
                $action->getId(),
                $action,
                'Action created: ' . $action->getName()
            );

            $this->entityManager->commit();
            return $this->formatAction($action);
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to create action: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Delete an action
     */
    public function deleteAction(int $actionId): bool
    {
        $this->entityManager->beginTransaction();
        try {
            /** @var Action|null $action */
            $action = $this->entityManager->find(Action::class, $actionId);
            if (!$action) {
                throw new ServiceException('Action not found', Response::HTTP_NOT_FOUND);
            }

            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'actions',
                $action->getId(),
                $action,
                'Action deleted: ' . $action->getName()
            );

            $this->entityManager->remove($action);
            $this->entityManager->flush();
            $this->entityManager->commit();
            return true;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to delete action: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
    }

    private function formatAction(Action $action): array
    {
        $trigger = $action->getActionTriggerType();
        $dataTable = $action->getDataTable();
        return [
            'id' => $action->getId(),
            'name' => $action->getName(),
            'action_trigger_type' => $trigger ? [
                'id' => $trigger->getId(),
                'type_code' => $trigger->getTypeCode(),
                'lookup_code' => $trigger->getLookupCode(),
                'lookup_value' => $trigger->getLookupValue(),
            ] : null,
            'config' => $action->getConfig() ? json_decode($action->getConfig(), true) : null,
            'data_table' => $dataTable ? [
                'id' => $dataTable->getId(),
                'name' => $dataTable->getName(),
                'displayName' => $dataTable->getDisplayName(),
            ] : null,
        ];
    }
}


