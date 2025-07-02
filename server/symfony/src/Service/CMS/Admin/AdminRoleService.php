<?php

namespace App\Service\CMS\Admin;

use App\Entity\Role;
use App\Entity\Permission;
use App\Repository\UserRepository;
use App\Service\Core\LookupService;
use App\Service\Core\UserContextAwareService;
use App\Service\Core\TransactionService;
use App\Service\Auth\UserContextService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

class AdminRoleService extends UserContextAwareService
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserContextService $userContextService,
        private readonly EntityManagerInterface $entityManagerInterface,
        private readonly UserRepository $userRepository,
        private readonly TransactionService $transactionService
    ) {
        parent::__construct($userContextService);
        $this->entityManager = $entityManagerInterface;
    }

    /**
     * Get roles with pagination, search, and sorting
     */
    public function getRoles(
        int $page = 1,
        int $pageSize = 20,
        ?string $search = null,
        ?string $sort = null,
        ?string $sortDirection = 'asc'
    ): array {
        if ($page < 1) $page = 1;
        if ($pageSize < 1 || $pageSize > 100) $pageSize = 20;
        if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'asc';

        $qb = $this->createRoleQueryBuilder();
        
        // Apply search filter
        if ($search) {
            $qb->andWhere('(r.name LIKE :search OR r.description LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        // Apply sorting
        $allowedSortFields = ['name', 'description'];
        if ($sort && in_array($sort, $allowedSortFields)) {
            $qb->orderBy('r.' . $sort, $sortDirection);
        } else {
            $qb->orderBy('r.name', 'asc');
        }

        // Get total count for pagination
        $countQb = clone $qb;
        $totalCount = $countQb->select('COUNT(r.id)')->getQuery()->getSingleScalarResult();

        // Apply pagination
        $qb->setFirstResult(($page - 1) * $pageSize)
           ->setMaxResults($pageSize);

        $roles = $qb->getQuery()->getResult();

        return [
            'roles' => array_map([$this, 'formatRoleForList'], $roles),
            'pagination' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'totalCount' => (int)$totalCount,
                'totalPages' => (int)ceil($totalCount / $pageSize),
                'hasNext' => $page < ceil($totalCount / $pageSize),
                'hasPrevious' => $page > 1
            ]
        ];
    }

    /**
     * Get single role by ID with full details including permissions
     */
    public function getRoleById(int $roleId): array
    {
        $role = $this->entityManager->getRepository(Role::class)->find($roleId);
        if (!$role) {
            throw new ServiceException('Role not found', Response::HTTP_NOT_FOUND);
        }

        return $this->formatRoleForDetail($role);
    }

    /**
     * Create new role
     */
    public function createRole(array $roleData): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $this->validateRoleData($roleData);

            $role = new Role();
            $role->setName($roleData['name']);
            $role->setDescription($roleData['description'] ?? null);

            $this->entityManager->persist($role);
            $this->entityManager->flush();

            // Add initial permissions if provided
            if (isset($roleData['permission_ids']) && is_array($roleData['permission_ids'])) {
                $this->addPermissionsToRoleInternal($role, $roleData['permission_ids']);
            }

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_INSERT,
                LookupService::TRANSACTION_BY_BY_USER,
                'roles',
                $role->getId(),
                $role,
                'Role created: ' . $role->getName()
            );

            $this->entityManager->commit();

            return $this->formatRoleForDetail($role);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Update existing role
     */
    public function updateRole(int $roleId, array $roleData): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $role = $this->entityManager->getRepository(Role::class)->find($roleId);
            if (!$role) {
                throw new ServiceException('Role not found', Response::HTTP_NOT_FOUND);
            }

            if (isset($roleData['description'])) {
                $role->setDescription($roleData['description']);
            }

            $this->entityManager->flush();

            // Update permissions if provided
            if (isset($roleData['permission_ids']) && is_array($roleData['permission_ids'])) {
                $this->updateRolePermissionsInternal($role, $roleData['permission_ids']);
            }

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'roles',
                $role->getId(),
                $role,
                'Role updated: ' . $role->getName()
            );

            $this->entityManager->commit();

            return $this->formatRoleForDetail($role);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Delete role
     */
    public function deleteRole(int $roleId): void
    {
        $this->entityManager->beginTransaction();
        
        try {
            $role = $this->entityManager->getRepository(Role::class)->find($roleId);
            if (!$role) {
                throw new ServiceException('Role not found', Response::HTTP_NOT_FOUND);
            }

            // Check if role has users
            if (!$role->getUsers()->isEmpty()) {
                throw new ServiceException('Cannot delete role with assigned users', Response::HTTP_CONFLICT);
            }

            // Log transaction before deletion
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'roles',
                $role->getId(),
                $role,
                'Role deleted: ' . $role->getName()
            );

            $this->entityManager->remove($role);
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Get role permissions
     */
    public function getRolePermissions(int $roleId): array
    {
        $role = $this->entityManager->getRepository(Role::class)->find($roleId);
        if (!$role) {
            throw new ServiceException('Role not found', Response::HTTP_NOT_FOUND);
        }

        return array_map([$this, 'formatPermissionForResponse'], $role->getPermissions()->toArray());
    }

    /**
     * Add permissions to role (bulk)
     */
    public function addPermissionsToRole(int $roleId, array $permissionIds): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $role = $this->entityManager->getRepository(Role::class)->find($roleId);
            if (!$role) {
                throw new ServiceException('Role not found', Response::HTTP_NOT_FOUND);
            }

            $this->addPermissionsToRoleInternal($role, $permissionIds);

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'roles',
                $role->getId(),
                false,
                'Permissions added to role: ' . $role->getName() . ' (Permission IDs: ' . implode(', ', $permissionIds) . ')'
            );

            $this->entityManager->commit();

            return $this->getRolePermissions($roleId);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Remove permissions from role (bulk)
     */
    public function removePermissionsFromRole(int $roleId, array $permissionIds): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $role = $this->entityManager->getRepository(Role::class)->find($roleId);
            if (!$role) {
                throw new ServiceException('Role not found', Response::HTTP_NOT_FOUND);
            }

            foreach ($permissionIds as $permissionId) {
                $permission = $this->entityManager->getRepository(Permission::class)->find($permissionId);
                if (!$permission) {
                    throw new ServiceException('Permission not found: ' . $permissionId, Response::HTTP_NOT_FOUND);
                }

                if ($role->getPermissions()->contains($permission)) {
                    $role->removePermission($permission);
                }
            }

            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'roles',
                $role->getId(),
                false,
                'Permissions removed from role: ' . $role->getName() . ' (Permission IDs: ' . implode(', ', $permissionIds) . ')'
            );

            $this->entityManager->commit();

            return $this->getRolePermissions($roleId);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Update role permissions (bulk replace)
     */
    public function updateRolePermissions(int $roleId, array $permissionIds): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $role = $this->entityManager->getRepository(Role::class)->find($roleId);
            if (!$role) {
                throw new ServiceException('Role not found', Response::HTTP_NOT_FOUND);
            }

            $this->updateRolePermissionsInternal($role, $permissionIds);

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'roles',
                $role->getId(),
                false,
                'Role permissions updated: ' . $role->getName() . ' (Permission IDs: ' . implode(', ', $permissionIds) . ')'
            );

            $this->entityManager->commit();

            return $this->getRolePermissions($roleId);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Get all available permissions
     */
    public function getAllPermissions(): array
    {
        $permissions = $this->entityManager->getRepository(Permission::class)
            ->createQueryBuilder('p')
            ->orderBy('p.name', 'asc')
            ->getQuery()
            ->getResult();

        return array_map([$this, 'formatPermissionForResponse'], $permissions);
    }

    /**
     * Create query builder for roles
     */
    private function createRoleQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->getRepository(Role::class)
            ->createQueryBuilder('r');
    }

    /**
     * Format role for list view
     */
    private function formatRoleForList(Role $role): array
    {
        return [
            'id' => $role->getId(),
            'name' => $role->getName(),
            'description' => $role->getDescription(),
            'permission_count' => $role->getPermissions()->count(),
            'user_count' => $role->getUsers()->count()
        ];
    }

    /**
     * Format role for detail view
     */
    private function formatRoleForDetail(Role $role): array
    {
        return [
            'id' => $role->getId(),
            'name' => $role->getName(),
            'description' => $role->getDescription(),
            'permission_count' => $role->getPermissions()->count(),
            'user_count' => $role->getUsers()->count(),
            'permissions' => array_map([$this, 'formatPermissionForResponse'], $role->getPermissions()->toArray()),
            'users' => array_map(function($user) {
                return [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName()
                ];
            }, $role->getUsers()->toArray())
        ];
    }

    /**
     * Format permission for response
     */
    private function formatPermissionForResponse(Permission $permission): array
    {
        return [
            'id' => $permission->getId(),
            'name' => $permission->getName(),
            'description' => $permission->getDescription()
        ];
    }

    /**
     * Validate role data
     */
    private function validateRoleData(array $data): void
    {
        if (empty($data['name'])) {
            throw new ServiceException('Role name is required', Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['name'])) {
            // Check for duplicate name
            $existingRole = $this->entityManager->getRepository(Role::class)
                ->findOneBy(['name' => $data['name']]);
            if ($existingRole) {
                throw new ServiceException('Role name already exists', Response::HTTP_CONFLICT);
            }
        }
    }

    /**
     * Internal method to add permissions to role (without transaction handling)
     */
    private function addPermissionsToRoleInternal(Role $role, array $permissionIds): void
    {
        foreach ($permissionIds as $permissionId) {
            $permission = $this->entityManager->getRepository(Permission::class)->find($permissionId);
            if (!$permission) {
                throw new ServiceException('Permission not found: ' . $permissionId, Response::HTTP_NOT_FOUND);
            }

            if (!$role->getPermissions()->contains($permission)) {
                $role->addPermission($permission);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Internal method to update role permissions (without transaction handling)
     */
    private function updateRolePermissionsInternal(Role $role, array $permissionIds): void
    {
        // Remove all existing permissions
        foreach ($role->getPermissions() as $permission) {
            $role->removePermission($permission);
        }

        // Add new permissions
        foreach ($permissionIds as $permissionId) {
            $permission = $this->entityManager->getRepository(Permission::class)->find($permissionId);
            if (!$permission) {
                throw new ServiceException('Permission not found: ' . $permissionId, Response::HTTP_NOT_FOUND);
            }

            $role->addPermission($permission);
        }

        $this->entityManager->flush();
    }
} 