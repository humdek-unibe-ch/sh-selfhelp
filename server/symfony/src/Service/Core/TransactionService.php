<?php

namespace App\Service\Core;

use App\Entity\Lookup;
use App\Entity\Transaction;
use App\Repository\LookupRepository;
use App\Service\Auth\UserContextService;
use App\Service\Core\LookupService;
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
        private readonly LookupRepository $lookupRepository
    ) {}

    /**
     * Log a transaction in the system
     *
     * @param string $tranType The transaction type code (e.g., 'create', 'update', 'delete')
     * @param string $tranBy The transaction by code (e.g., 'user', 'system')
     * @param string|null $tableName The table name affected by the transaction
     * @param int|null $entryId The ID of the affected record
     * @param bool $logRow Whether to log the entire row data
     * @param string|null $verbalLog Custom verbal log message
     * @return Transaction The created transaction entity
     */
    public function logTransaction(
        string $tranType = LookupService::TRANSACTION_TYPES_INSERT,
        string $tranBy = LookupService::TRANSACTION_BY_BY_USER,
        ?string $tableName = null,
        ?int $entryId = null,
        bool $logRow = false,
        ?string $verbalLog = null
    ): Transaction {
        // Get current user ID
        $userId = $this->userContextService->getCurrentUser()?->getId() ?? 0;
        
        // Create log data
        $log = [
            'verbal_log' => $verbalLog ?: ('Transaction type: `' . $tranType . '` from table: `' . $tableName . '` triggered ' . $tranBy),
            'url' => $userId > 0 ? ($this->requestStack->getCurrentRequest()?->getRequestUri() ?? '') : '',
            'session' => $userId > 0 ? session_id() : ''
        ];
        
        // If table name and entry ID are provided, and log row is true, fetch the row data
        if ($tableName && $entryId && $logRow) {
            $conn = $this->entityManager->getConnection();
            $stmt = $conn->prepare('SELECT * FROM ' . $tableName . ' WHERE id = :id');
            $result = $stmt->executeQuery(['id' => $entryId]);
            $entry = $result->fetchAssociative();
            
            if ($entry) {
                $log['table_row_entry'] = $entry;
            }
        }
        
        // Get lookup IDs for transaction type and by
        $transactionTypeId = $this->lookupRepository->findOneBy([
            'typeCode' => LookupService::TRANSACTION_TYPES,
            'lookupCode' => $tranType
        ])?->getId();
        
        $transactionById = $this->lookupRepository->findOneBy([
            'typeCode' => LookupService::TRANSACTION_BY,
            'lookupCode' => $tranBy
        ])?->getId();
        
        // Create transaction entity
        $transaction = new Transaction();
        $transaction->setIdTransactionTypes($transactionTypeId);
        $transaction->setIdTransactionBy($transactionById);
        $transaction->setIdUsers($userId);
        $transaction->setTableName($tableName);
        $transaction->setIdTableName($entryId);
        $transaction->setTransactionLog(json_encode($log));
        $transaction->setTransactionTime(new \DateTime());
        
        // Persist and flush
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
        
        return $transaction;
    }    
}
