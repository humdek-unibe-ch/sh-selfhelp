<?php

namespace App\Service\CMS\Admin;

use App\Entity\Group;
use App\Entity\AclGroup;
use App\Entity\Page;
use App\Repository\UserRepository;
use App\Service\Core\LookupService;
use App\Service\Core\UserContextAwareService;
use App\Service\Core\TransactionService;
use App\Service\Auth\UserContextService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

class AdminGroupService extends UserContextAwareService
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
     * Get groups with pagination, search, and sorting
     */
    public function getGroups(
        int $page = 1,
        int $pageSize = 20,
        ?string $search = null,
        ?string $sort = null,
        ?string $sortDirection = 'asc'
    ): array {
        if ($page < 1) $page = 1;
        if ($pageSize < 1 || $pageSize > 100) $pageSize = 20;
        if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'asc';

        $qb = $this->createGroupQueryBuilder();
        
        // Apply search filter
        if ($search) {
            $qb->andWhere('(g.name LIKE :search OR g.description LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        // Apply sorting
        $allowedSortFields = ['name', 'description', 'requires_2fa'];
        if ($sort && in_array($sort, $allowedSortFields)) {
            $qb->orderBy('g.' . $sort, $sortDirection);
        } else {
            $qb->orderBy('g.name', 'asc');
        }

        // Get total count for pagination
        $countQb = clone $qb;
        $totalCount = $countQb->select('COUNT(g.id)')->getQuery()->getSingleScalarResult();

        // Apply pagination
        $qb->setFirstResult(($page - 1) * $pageSize)
           ->setMaxResults($pageSize);

        $groups = $qb->getQuery()->getResult();

        return [
            'groups' => array_map([$this, 'formatGroupForList'], $groups),
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
     * Get single group by ID with full details including ACLs
     */
    public function getGroupById(int $groupId): array
    {
        $group = $this->entityManager->getRepository(Group::class)->find($groupId);
        if (!$group) {
            throw new ServiceException('Group not found', Response::HTTP_NOT_FOUND);
        }

        return $this->formatGroupForDetail($group);
    }

    /**
     * Create new group
     */
    public function createGroup(array $groupData): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $this->validateGroupData($groupData, true);

            $group = new Group();
            $group->setName($groupData['name']);
            $group->setDescription($groupData['description'] ?? '');
            $group->setRequires2fa($groupData['requires_2fa'] ?? false);
            
            if (isset($groupData['id_group_types'])) {
                $group->setIdGroupTypes($groupData['id_group_types']);
            }

            $this->entityManager->persist($group);
            $this->entityManager->flush();

            // Create initial ACLs if provided
            if (isset($groupData['acls']) && is_array($groupData['acls'])) {
                $this->updateGroupAclsInternal($group, $groupData['acls']);
            }

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_INSERT,
                LookupService::TRANSACTION_BY_BY_USER,
                'groups',
                $group->getId(),
                $group,
                'Group created: ' . $group->getName()
            );

            $this->entityManager->commit();

            return $this->formatGroupForDetail($group);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Update existing group
     */
    public function updateGroup(int $groupId, array $groupData): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $group = $this->entityManager->getRepository(Group::class)->find($groupId);
            if (!$group) {
                throw new ServiceException('Group not found', Response::HTTP_NOT_FOUND);
            }

            $this->validateGroupData($groupData, false);

            if (isset($groupData['name'])) {
                $group->setName($groupData['name']);
            }
            if (isset($groupData['description'])) {
                $group->setDescription($groupData['description']);
            }
            if (isset($groupData['requires_2fa'])) {
                $group->setRequires2fa($groupData['requires_2fa']);
            }
            if (isset($groupData['id_group_types'])) {
                $group->setIdGroupTypes($groupData['id_group_types']);
            }

            $this->entityManager->flush();

            // Update ACLs if provided
            if (isset($groupData['acls']) && is_array($groupData['acls'])) {
                $this->updateGroupAclsInternal($group, $groupData['acls']);
            }

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'groups',
                $group->getId(),
                $group,
                'Group updated: ' . $group->getName()
            );

            $this->entityManager->commit();

            return $this->formatGroupForDetail($group);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Delete group
     */
    public function deleteGroup(int $groupId): void
    {
        $this->entityManager->beginTransaction();
        
        try {
            $group = $this->entityManager->getRepository(Group::class)->find($groupId);
            if (!$group) {
                throw new ServiceException('Group not found', Response::HTTP_NOT_FOUND);
            }

            // Check if group has users
            if (!$group->getUsersGroups()->isEmpty()) {
                throw new ServiceException('Cannot delete group with assigned users', Response::HTTP_CONFLICT);
            }

            // Log transaction before deletion
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'groups',
                $group->getId(),
                $group,
                'Group deleted: ' . $group->getName()
            );

            $this->entityManager->remove($group);
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Get group ACLs
     */
    public function getGroupAcls(int $groupId): array
    {
        $group = $this->entityManager->getRepository(Group::class)->find($groupId);
        if (!$group) {
            throw new ServiceException('Group not found', Response::HTTP_NOT_FOUND);
        }

        $acls = $this->entityManager->getRepository(AclGroup::class)
            ->createQueryBuilder('ag')
            ->select('ag, p')
            ->leftJoin('ag.page', 'p')
            ->where('ag.group = :group')
            ->setParameter('group', $group)
            ->orderBy('p.keyword', 'asc')
            ->getQuery()
            ->getResult();

        return array_map([$this, 'formatAclForResponse'], $acls);
    }

    /**
     * Update group ACLs (bulk update)
     */
    public function updateGroupAcls(int $groupId, array $aclsData): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $group = $this->entityManager->getRepository(Group::class)->find($groupId);
            if (!$group) {
                throw new ServiceException('Group not found', Response::HTTP_NOT_FOUND);
            }

            $this->updateGroupAclsInternal($group, $aclsData);

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'acl_groups',
                $group->getId(),
                false,
                'Group ACLs updated: ' . $group->getName() . ' (' . count($aclsData) . ' ACLs)'
            );

            $this->entityManager->commit();

            return $this->getGroupAcls($groupId);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Create query builder for groups
     */
    private function createGroupQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->getRepository(Group::class)
            ->createQueryBuilder('g');
    }

    /**
     * Format group for list view
     */
    private function formatGroupForList(Group $group): array
    {
        return [
            'id' => $group->getId(),
            'name' => $group->getName(),
            'description' => $group->getDescription(),
            'id_group_types' => $group->getIdGroupTypes(),
            'requires_2fa' => $group->isRequires2fa(),
            'user_count' => $group->getUsersGroups()->count()
        ];
    }

    /**
     * Format group for detail view
     */
    private function formatGroupForDetail(Group $group): array
    {
        $acls = $this->getGroupAcls($group->getId());
        
        return [
            'id' => $group->getId(),
            'name' => $group->getName(),
            'description' => $group->getDescription(),
            'id_group_types' => $group->getIdGroupTypes(),
            'requires_2fa' => $group->isRequires2fa(),
            'user_count' => $group->getUsersGroups()->count(),
            'users' => array_map(function($ug) {
                $user = $ug->getUser();
                return [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName()
                ];
            }, $group->getUsersGroups()->toArray()),
            'acls' => $acls
        ];
    }

    /**
     * Format ACL for response
     */
    private function formatAclForResponse(AclGroup $acl): array
    {
        return [
            'page_id' => $acl->getPage()->getId(),
            'page_keyword' => $acl->getPage()->getKeyword(),
            'page_title' => $acl->getPage()->getTitle(),
            'acl_select' => $acl->getAclSelect(),
            'acl_insert' => $acl->getAclInsert(),
            'acl_update' => $acl->getAclUpdate(),
            'acl_delete' => $acl->getAclDelete()
        ];
    }

    /**
     * Validate group data
     */
    private function validateGroupData(array $data, bool $isCreate): void
    {
        if ($isCreate && empty($data['name'])) {
            throw new ServiceException('Group name is required', Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['name'])) {
            // Check for duplicate name
            $existingGroup = $this->entityManager->getRepository(Group::class)
                ->findOneBy(['name' => $data['name']]);
            if ($existingGroup) {
                throw new ServiceException('Group name already exists', Response::HTTP_CONFLICT);
            }
        }
    }

    /**
     * Validate ACL data
     */
    private function validateAclData(array $data): void
    {
        if (!isset($data['page_id']) || !is_numeric($data['page_id'])) {
            throw new ServiceException('Valid page_id is required for ACL', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Internal method to update group ACLs (without transaction handling)
     */
    private function updateGroupAclsInternal(Group $group, array $aclsData): void
    {
        foreach ($aclsData as $aclData) {
            $this->validateAclData($aclData);
            
            $page = $this->entityManager->getRepository(Page::class)->find($aclData['page_id']);
            if (!$page) {
                throw new ServiceException('Page not found: ' . $aclData['page_id'], Response::HTTP_NOT_FOUND);
            }

            // Find existing ACL or create new one
            $acl = $this->entityManager->getRepository(AclGroup::class)
                ->findOneBy(['group' => $group, 'page' => $page]);

            if (!$acl) {
                $acl = new AclGroup();
                $acl->setGroup($group);
                $acl->setPage($page);
                $this->entityManager->persist($acl);
            }

            $acl->setAclSelect($aclData['acl_select'] ?? true);
            $acl->setAclInsert($aclData['acl_insert'] ?? false);
            $acl->setAclUpdate($aclData['acl_update'] ?? false);
            $acl->setAclDelete($aclData['acl_delete'] ?? false);
        }

        $this->entityManager->flush();
    }
} 