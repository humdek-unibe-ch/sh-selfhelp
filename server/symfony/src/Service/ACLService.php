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
        private readonly Connection $connection,
    ) {}

    /**
     * Check if user has access to a page
     *
     * @param int|string|null $userId The user ID or user identifier (may be null or string from Security)
     * @param int $pageId The page ID
     * @param string $accessType The type of access to check (select, insert, update, delete)
     * @return bool True if user has access, false otherwise
     */
    public function hasAccess(int|string|null $userId, int $pageId,  $accessType = 'select'): bool
    {
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

        // Call stored procedure get_user_acl(userId, pageId)
        $sql = 'CALL get_user_acl(:userId, :pageId)';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([
            'userId' => $userId,
            'pageId' => $pageId
        ])->fetchAssociative();

        // If no result, deny access
        if (!$result || !array_key_exists($aclColumn, $result)) {
            return false;
        }
        // Grant if column is 1
        return (int)$result[$aclColumn] === 1;
    }
}
