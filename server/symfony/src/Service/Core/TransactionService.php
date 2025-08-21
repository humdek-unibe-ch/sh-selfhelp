<?php

namespace App\Service\Core;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Auth\UserContextService;
use App\Service\Core\LookupService;
use App\Service\Cache\Core\ReworkedCacheService;
use App\Util\EntityUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service for logging transactions in the system
 */
class TransactionService
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserContextService $userContextService,
        private readonly RequestStack $requestStack,
        private readonly LookupService $lookupService,
        private readonly ReworkedCacheService $cache
    ) {}

    /**
     * Log a transaction in the system
     *
     * @param string $tranType The transaction type code (e.g., 'create', 'update', 'delete')
     * @param string $tranBy The transaction by code (e.g., 'user', 'system')
     * @param string|null $tableName The table name affected by the transaction
     * @param int|null $entryId The ID of the affected record
     * @param bool|object $logRow Whether to log the entire row data or the actual row object to log
     * @param string|null $verbalLog Custom verbal log message
     * @return Transaction The created transaction entity
     */
    public function logTransaction(
        string $tranType = LookupService::TRANSACTION_TYPES_INSERT,
        string $tranBy = LookupService::TRANSACTION_BY_BY_USER,
        ?string $tableName = null,
        ?int $entryId = null,
        $logRow = false,
        ?string $verbalLog = null
    ): Transaction {
        // Get current user ID
        $userId = $this->userContextService->getCurrentUser()?->getId() ?? null;
        
        // Create log data
        $log = [
            'verbal_log' => $verbalLog ?: ('Transaction type: `' . $tranType . '` from table: `' . $tableName . '` triggered ' . $tranBy),
            'url' => $userId > 0 ? ($this->requestStack->getCurrentRequest()?->getRequestUri() ?? '') : '',
            'session' => $userId > 0 ? session_id() : ''
        ];
        
        // Handle row data logging
        if ($tableName && $entryId) {
            // If logRow is an object, use it directly as the row data
            if (is_object($logRow)) {
                // Handle Doctrine entities and other objects
                $entityData = EntityUtil::convertEntityToArray($logRow);
                $log['table_row_entry'] = $entityData;
            } 
            // If logRow is true, fetch the row data from the database
            elseif ($logRow === true) {
                $conn = $this->entityManager->getConnection();
                $stmt = $conn->prepare('SELECT * FROM ' . $tableName . ' WHERE id = :id');
                $stmt->bindValue('id', $entryId, \PDO::PARAM_INT);
                $result = $stmt->executeQuery();
                $entry = $result->fetchAssociative();
                
                if ($entry) {
                    $log['table_row_entry'] = $entry;
                }
            }
        }
        
        // Create transaction entity
        $transaction = new Transaction();
        
        // Check if we're in an active transaction to avoid EntityManager conflicts
        
            // Safe to do lookup queries when not in transaction
            $transactionType = $this->lookupService->findByTypeAndCode(
                LookupService::TRANSACTION_TYPES,
                $tranType
            );
            
            $transactionBy = $this->lookupService->findByTypeAndCode(
                LookupService::TRANSACTION_BY,
                $tranBy
            );
            
            // Set the entity relationships directly
            if ($transactionType) {
                $transaction->setTransactionType($transactionType);
            }
            
            if ($transactionBy) {
                $transaction->setTransactionBy($transactionBy);
            }
       
        
        // Set user if available
        if ($userId) {
            $user = $this->entityManager->getReference(User::class, $userId);
            $transaction->setUser($user);
        }
        
        $transaction->setTableName($tableName);
        $transaction->setIdTableName($entryId);
        $transaction->setTransactionLog(json_encode($log));
        $transaction->setTransactionTime(new \DateTime());
        
        // Persist the transaction
        $this->entityManager->persist($transaction);
        
        $this->entityManager->flush();
        
        return $transaction;
    }
    
    /**
     * Get transaction type ID by code without doing database queries
     * This uses a hardcoded mapping to avoid DB queries during active transactions
     */
    private function getTransactionTypeIdByCode(string $code): ?int
    {
        // Hardcoded mapping for common transaction types
        // This avoids database queries during active transactions
        $mapping = [
            LookupService::TRANSACTION_TYPES_INSERT => 1,
            LookupService::TRANSACTION_TYPES_UPDATE => 2,
            LookupService::TRANSACTION_TYPES_DELETE => 3,
            LookupService::TRANSACTION_TYPES_SELECT => 4,
            LookupService::TRANSACTION_TYPES_STATUS_CHANGE => 5,
        ];
        
        return $mapping[$code] ?? null;
    }
    
    /**
     * Get transaction by ID by code without doing database queries
     * This uses a hardcoded mapping to avoid DB queries during active transactions
     */
    private function getTransactionByIdByCode(string $code): ?int
    {
        // Hardcoded mapping for common transaction by types
        // This avoids database queries during active transactions
        $mapping = [
            LookupService::TRANSACTION_BY_BY_USER => 1,
            LookupService::TRANSACTION_BY_BY_SYSTEM => 2,
            LookupService::TRANSACTION_BY_BY_ANONYMOUS_USER => 3,
            LookupService::TRANSACTION_BY_BY_CRON_JOB => 4,
            LookupService::TRANSACTION_BY_BY_SYSTEM_USER => 5,
        ];
        
        return $mapping[$code] ?? null;
    }
}
