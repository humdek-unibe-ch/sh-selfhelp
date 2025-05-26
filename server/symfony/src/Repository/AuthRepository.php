<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;
use App\Repository\User2faCodeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Exception;

class AuthRepository
{
    private Connection $connection;
    private User2faCodeRepository $user2faCodeRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        Connection $connection,
        User2faCodeRepository $user2faCodeRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    ) {
        $this->connection = $connection;
        $this->user2faCodeRepository = $user2faCodeRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Checks if 2FA is required for a given user.
     *
     * @param int $userId
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     */
    public function is2faRequired(int $userId): bool
    {
        $sql = "SELECT SUM(g.requires_2fa) AS requires_2fa
                FROM users u
                INNER JOIN users_groups ug ON (ug.id_users = u.id)
                INNER JOIN `groups` g ON (ug.id_groups = g.id)
                WHERE u.id = :user_id";
        $result = $this->connection->fetchAssociative($sql, ['user_id' => $userId]);
        return $result && $result['requires_2fa'] > 0;
    }

    /**
     * Generates and stores a 2FA code for the user.
     *
     * @param int $userId
     * @return void
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception // For random_int
     */
    public function generateAndStore2faCode(int $userId): void
    {
        $code = random_int(100000, 999999);
        $expiresAt = (new DateTime())->modify('+10 minutes');

        $this->user2faCodeRepository->insert($userId, (string)$code, $expiresAt);
    }

    public function verify2faCode(int $userId, string $code): bool
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            // Or throw an exception, depending on desired error handling
            return false;
        }

        $user2faCodeEntity = $this->user2faCodeRepository->findValidCodeForUser($user, $code);

        if ($user2faCodeEntity) {
            $user2faCodeEntity->setIsUsed(true);
            $this->entityManager->persist($user2faCodeEntity); // persist might be redundant if entity is already managed

            $user->setLastLogin(new \DateTimeImmutable()); // Added lastLogin update
            $this->entityManager->persist($user); // Ensure user entity changes are persisted

            $this->entityManager->flush();
            return true;
        }

        return false;
    }
}
