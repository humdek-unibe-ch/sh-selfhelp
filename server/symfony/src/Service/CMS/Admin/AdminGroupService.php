<?php

namespace App\Service\CMS\Admin;

use App\Entity\Group;
use App\Entity\AclGroup;
use App\Entity\Page;
use App\Repository\UserRepository;
use App\Service\Core\LookupService;
use App\Service\Core\BaseService;
use App\Service\Core\TransactionService;
use App\Service\Cache\Core\ReworkedCacheService;
use App\Service\Auth\UserContextService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

class AdminGroupService extends BaseService
{

    public function __construct(
        private readonly UserContextService $userContextService,
        private readonly EntityManagerInterface $entityManagerInterface,
        private readonly UserRepository $userRepository,
        private readonly TransactionService $transactionService,
        private readonly ReworkedCacheService $cache,
        private readonly EntityManagerInterface $entityManager
    ) {
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
        if ($page < 1)
            $page = 1;
        if ($pageSize < 1 || $pageSize > 100)
            $pageSize = 20;
        if (!in_array($sortDirection, ['asc', 'desc']))
            $sortDirection = 'asc';

        // Create cache key based on parameters
        $cacheKey = "groups_list_{$page}_{$pageSize}_" . md5(($search ?? '') . ($sort ?? '') . $sortDirection);

        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_GROUPS)
            ->getList(
                $cacheKey,
                fn() => $this->fetchGroupsFromDatabase($page, $pageSize, $search, $sort, $sortDirection)
            );
    }


    private function fetchGroupsFromDatabase(int $page, int $pageSize, ?string $search, ?string $sort, string $sortDirection): array
    {
        $qb = $this->entityManager->getRepository(Group::class)
        ->createQueryBuilder('g');

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
                'totalCount' => (int) $totalCount,
                'totalPages' => (int) ceil($totalCount / $pageSize),
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
        $cacheKey = "group_{$groupId}";

        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_GROUPS)
            ->getItem($cacheKey, function () use ($groupId) {
                $group = $this->entityManager->getRepository(Group::class)->find($groupId);
                if (!$group) {
                    throw new ServiceException('Group not found', Response::HTTP_NOT_FOUND);
                }
                return $this->formatGroupForDetail($group);
            });
    }

    /**
     * Create new group
     */
    public function createGroup(array $groupData): array
    {
        $this->entityManager->beginTransaction();

        try {
            $this->validateGroupData($groupData);

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

            // Invalidate cache after create
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_GROUPS)
                ->invalidateAllListsInCategory();

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

            // Invalidate cache after update
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_GROUPS)
                ->invalidateItemAndLists("group_{$groupId}");
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_GROUPS)
                ->invalidateItemAndLists("group_acls_{$groupId}");

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

            // Invalidate cache after delete
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_GROUPS)
                ->invalidateAllListsInCategory();
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
        $cacheKey = "group_acls_{$groupId}";

        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_GROUPS)
            ->getItem($cacheKey, function () use ($groupId) {
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
            });
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

            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_GROUPS)
                ->invalidateItemAndLists("group_acls_{$groupId}");
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_GROUPS)
                ->invalidateItemAndLists("group_{$groupId}");

            return $this->getGroupAcls($groupId);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
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
            'users_count' => $group->getUsersGroups()->count()
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
            'users_count' => $group->getUsersGroups()->count(),
            'users' => array_map(function ($ug) {
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
            'page_url' => $acl->getPage()->getUrl(),
            'acl_select' => $acl->getAclSelect(),
            'acl_insert' => $acl->getAclInsert(),
            'acl_update' => $acl->getAclUpdate(),
            'acl_delete' => $acl->getAclDelete()
        ];
    }

    /**
     * Validate group data
     */
    private function validateGroupData(array $data): void
    {
        if (empty($data['name'])) {
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
        // First, remove all existing ACL permissions for this group
        $existingAcls = $this->entityManager->getRepository(AclGroup::class)
            ->findBy(['group' => $group]);

        foreach ($existingAcls as $existingAcl) {
            $this->entityManager->remove($existingAcl);
        }

        // Flush the deletions first to avoid constraint violations
        $this->entityManager->flush();

        // Then, add only the ACL permissions that are passed in the request
        if (!empty($aclsData)) {
            // Validate all ACL data first
            foreach ($aclsData as $aclData) {
                $this->validateAclData($aclData);
            }

            // Batch load all pages in one query to avoid N+1
            $pageIds = array_column($aclsData, 'page_id');
            $pages = $this->entityManager->getRepository(Page::class)
                ->createQueryBuilder('p')
                ->where('p.id IN (:pageIds)')
                ->setParameter('pageIds', $pageIds)
                ->getQuery()
                ->getResult();

            // Create a map for quick lookup
            $pageMap = [];
            foreach ($pages as $page) {
                $pageMap[$page->getId()] = $page;
            }

            // Create ACLs
            foreach ($aclsData as $aclData) {
                $pageId = $aclData['page_id'];
                if (!isset($pageMap[$pageId])) {
                    throw new ServiceException('Page not found: ' . $pageId, Response::HTTP_NOT_FOUND);
                }

                // Create new ACL
                $acl = new AclGroup();
                $acl->setGroup($group);
                $acl->setPage($pageMap[$pageId]);
                $acl->setAclSelect($aclData['acl_select'] ?? true);
                $acl->setAclInsert($aclData['acl_insert'] ?? false);
                $acl->setAclUpdate($aclData['acl_update'] ?? false);
                $acl->setAclDelete($aclData['acl_delete'] ?? false);

                $this->entityManager->persist($acl);
            }
        }

        // Flush the insertions
        $this->entityManager->flush();
    }
}