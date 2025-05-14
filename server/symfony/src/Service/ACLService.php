<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;

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
        // private readonly Security $security
    ) {
    }

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
        $resolvedUserId = $this->resolveUserId($userId);

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
            'user_id' => $resolvedUserId,
            'page_id' => $pageId,
            'access_type' => $accessType
        ]);
        return $result && $result['access_count'] > 0;
    }

    /**
     * Resolve the user ID from various possible sources (int, string, null)
     * Falls back to guest user ID if not authenticated
     *
     * @param int|string|null $userId
     * @return int
     */
    private function resolveUserId(int|string|null $userId): int
    {
        // Guest user ID constant (should match your DB guest user)
        $guestUserId = 1;
        if (is_numeric($userId) && $userId > 0) {
            return (int)$userId;
        }
        // If userId is a string (e.g., email or UUID), resolve to user ID from DB
        if (is_string($userId) && !empty($userId)) {
            $result = $this->connection->fetchAssociative(
                'SELECT id FROM users WHERE email = :email',
                ['email' => $userId]
            );
            if ($result && isset($result['id'])) {
                return (int)$result['id'];
            }
        }
        // Try to get user ID from Symfony Security component
        // $user = $this->security->getUser();
        // if ($user && method_exists($user, 'getUserIdentifier')) {
        //     return $user->getUserIdentifier();
        // }
        // Fallback to guest
        return $guestUserId;
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