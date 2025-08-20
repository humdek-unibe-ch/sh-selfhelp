<?php

namespace App\Service\ACL;

use App\Entity\AclUser;
use App\Entity\Group;
use App\Entity\Page;
use App\Entity\User;
use App\Service\Cache\Core\ReworkedCacheService;
use Doctrine\DBAL\Connection;
use App\Repository\AclRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * ACL service
 * 
 * Handles access control list operations
 */
class ACLService
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly AclRepository $aclRepository,
        private readonly ReworkedCacheService $cache,
    ) {}

    /**
     * Check if user has access to a page
     *
     * @param int|string|null $userId The user ID or user identifier (may be null or string from Security)
     * @param int $pageId The page ID
     * @param string $accessType The type of access to check (select, insert, update, delete)
     * @return bool True if user has access, false otherwise
     */
    public function hasAccess(int|string|null $userId, int $pageId, string $accessType = 'select'): bool
    {
        // Handle null or non-integer userId
        if ($userId === null) {
            $userId = 1; // Guest user ID
        } elseif (!is_int($userId)) {
            // Convert string user ID to int if needed
            $userId = (int)$userId;
        }

        // Map accessType to column
        $modeMap = [
            'select' => 'acl_select',
            'insert' => 'acl_insert',
            'update' => 'acl_update',
            'delete' => 'acl_delete',
        ];
        if (!isset($modeMap[$accessType])) {
            throw new \InvalidArgumentException("Unknown access type: $accessType");
        }
        $aclColumn = $modeMap[$accessType];

        // Get ACL for specific page using repository (cached)
        $results = $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_PERMISSIONS)
            ->getItem("user_acl_{$userId}_{$pageId}", fn() => $this->aclRepository->getUserAcl($userId, $pageId));
        
        // If no results or empty array, deny access
        if (empty($results)) {
            return false;
        }
        
        // The repository returns an array of pages, but since we're querying for a specific page,
        // we should only have one result
        $result = $results[0] ?? null;
        
        // If no result or ACL column doesn't exist, deny access
        if (!$result || !array_key_exists($aclColumn, $result)) {
            return false;
        }
        
        // Grant if column is 1
        return (int)$result[$aclColumn] === 1;
    }
    
    /**
     * Get all pages with ACL information for a user
     * 
     * This is cached in memory for the duration of the request
     * so it's efficient to call multiple times
     *
     * @param int|string|null $userId The user ID
     * @return array Array of pages with ACL information
     */
    public function getAllUserAcls(int|string|null $userId): array
    {
        // Handle null or non-integer userId
        if ($userId === null) {
            $userId = 1; // Guest user ID
        } elseif (!is_int($userId)) {
            // Convert string user ID to int if needed
            $userId = (int)$userId;
        }
        
        // Use the repository to get all ACLs (cached)
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_PERMISSIONS)
            ->getItem("user_acl_{$userId}", fn() => $this->aclRepository->getUserAcl($userId));
    }

    /**
     * Add or update a group ACL for a page
     */
    public function addGroupAcl(Page $page, Group $group, bool $select, bool $insert, bool $update, bool $delete, EntityManagerInterface $em): void
    {
        $aclGroup = new \App\Entity\AclGroup();
        $aclGroup->setGroup($group)
            ->setPage($page)
            ->setAclSelect($select)
            ->setAclInsert($insert)
            ->setAclUpdate($update)
            ->setAclDelete($delete);
        $em->persist($aclGroup);
        $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_PERMISSIONS)
            ->invalidateAllListsInCategory();
    }   

    /**
     * Add or update a user ACL for a page
     */
    public function addUserAcl(Page $page, User $user, bool $select, bool $insert, bool $update, bool $delete, EntityManagerInterface $em): void
    {
        $aclUser = new AclUser();
        $aclUser->setUser($user)
            ->setPage($page)
            ->setAclSelect($select)
            ->setAclInsert($insert)
            ->setAclUpdate($update)
            ->setAclDelete($delete);
        $em->persist($aclUser);
        $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_PERMISSIONS)
            ->invalidateAllListsInCategory();
    }
}
