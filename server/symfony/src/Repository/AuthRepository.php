<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;
use App\Repository\User2faCodeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
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
        EntityManagerInterface $entityManager
    ) {
        $this->connection = $connection;
        $this->user2faCodeRepository = $user2faCodeRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
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
            return false;
        }

        $user2faCodeEntity = $this->user2faCodeRepository->findValidCodeForUser($user, $code);

        if ($user2faCodeEntity) {
            $user2faCodeEntity->setIsUsed(true);
            $this->entityManager->persist($user2faCodeEntity);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }
}
