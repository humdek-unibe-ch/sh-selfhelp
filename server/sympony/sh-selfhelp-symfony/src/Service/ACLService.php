<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

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
        private readonly Connection $connection
    ) {
    }

    /**
     * Check if user has access to a page
     * 
     * @param int $userId The user ID
     * @param int $pageId The page ID
     * @param string $accessType The type of access to check (select, insert, update, delete)
     * @return bool True if user has access, false otherwise
     */
    public function hasAccess(int $userId, int $pageId, string $accessType = 'select'): bool
    {
        // Guest user ID constant (assuming it's defined in your system)
        $guestUserId = 1; // Replace with your actual guest user ID
        
        // If user is not logged in, use guest user ID
        $userId = $userId ?: $guestUserId;
        
        // Check if page exists
        $page = $this->connection->fetchAssociative(
            'SELECT is_open_access FROM pages WHERE id = :id',
            ['id' => $pageId]
        );
        
        if (!$page) {
            return false;
        }
        
        // If page is open access, allow access
        if ($page['is_open_access']) {
            return true;
        }
        
        // Check user roles and permissions
        $sql = <<<SQL
        SELECT COUNT(*) as access_count
        FROM users_roles ur
        JOIN roles_permissions rp ON ur.id_roles = rp.id_roles
        JOIN permissions p ON rp.id_permissions = p.id
        WHERE ur.id_users = :user_id
        AND p.page_id = :page_id
        AND p.type = :access_type
        SQL;
        
        $result = $this->connection->fetchAssociative($sql, [
            'user_id' => $userId,
            'page_id' => $pageId,
            'access_type' => $accessType
        ]);
        
        return $result && $result['access_count'] > 0;
    }
    
    /**
     * Set current user ACLs
     * 
     * This method would typically be called after authentication to set up the user's permissions
     * 
     * @param int|null $userId The user ID, or null for guest user
     * @return void
     */
    public function setCurrentUserACLs(?int $userId = null): void
    {
        // In Symfony, this is typically handled by the security system
        // This method is included for compatibility with your existing code
        // but would likely be refactored in a full migration
    }
}