<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\User2faCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\DBAL\Connection;

class LoginService
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;
    private User2faCodeRepository $user2faCodeRepository;
    private UserRepository $userRepository;
    private Connection $db;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        User2faCodeRepository $user2faCodeRepository,
        UserRepository $userRepository,
        Connection $db
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->user2faCodeRepository = $user2faCodeRepository;
        $this->userRepository = $userRepository;
        $this->db = $db;
    }

    /**
     * Validate user credentials (username/email and password).
     * Returns user array, '2fa' (as string) if two-factor is required, or null if invalid.
     *
     * @param string $user
     * @param string $password
     * @return bool|User
     */
    public function validateUser(string $user, string $password): bool|User
    {
        // Find the user by email using UserRepository
        $userEntity = $this->entityManager->getRepository(User::class)->findOneByEmail($user);
        if (!$userEntity || !password_verify($password, $userEntity->getPassword())) {
            return false;
        }
        // If the user is found check if 2fa is required
        if ($this->is2faRequired($userEntity->getId())) {
            $userEntity->setTwoFactorRequired(true);
        }
        return $userEntity;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array|string|null
     */
    private function checkCredentialsByUsername(string $username, string $password)
    {
        $sql = "SELECT u.id, u.password, g.name AS gender, g.id AS id_gender, id_languages FROM users AS u
            LEFT JOIN genders AS g ON g.id = u.id_genders
            WHERE u.name = :username AND u.blocked = 0 LIMIT 1";
        $user = $this->db->fetchAssociative($sql, ['username' => $username]);
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            if ($this->is2faRequired($user['id'])) {
                $this->generate2faCode($user);
                return '2fa';
            }
            $this->updateTimestamp($user['id']);
            return $user;
        }
        return null;
    }

    /**
     * @param string $email
     * @param string $password
     * @return array|string|null
     */
    private function checkCredentialsByEmail(string $email, string $password)
    {
        $sql = "SELECT u.id, u.password, u.name AS user_name, g.name AS gender, g.id AS id_gender, id_languages FROM users AS u
            LEFT JOIN genders AS g ON g.id = u.id_genders
            WHERE u.email = :email AND u.blocked = 0 LIMIT 1";
        $user = $this->db->fetchAssociative($sql, ['email' => $email]);
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            if ($this->is2faRequired($user['id'])) {
                $this->generate2faCode($user);
                return '2fa';
            }
            $this->updateTimestamp($user['id']);
            return $user;
        }
        return null;
    }

    private function is2faRequired(int $userId): bool
    {
        $sql = "SELECT SUM(g.requires_2fa) AS requires_2fa
                FROM users u
                INNER JOIN users_groups ug ON (ug.id_users = u.id)
                INNER JOIN `groups` g ON (ug.id_groups = g.id)
                WHERE u.id = :user_id";
        $result = $this->db->fetchAssociative($sql, ['user_id' => $userId]);
        return $result && $result['requires_2fa'] > 0;
    }

    private function generate2faCode(array $user): void
    {
        $code = random_int(100000, 999999);
        $sql = "UPDATE users SET 2fa_code = :code, 2fa_expires = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE id = :id";
        $this->db->executeStatement($sql, [
            'code' => $code,
            'id' => $user['id']
        ]);
    }

    private function updateTimestamp(int $userId): void
    {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $this->db->executeStatement($sql, ['id' => $userId]);
    }

    /**
     * Verify 2FA code for a user using Doctrine entities.
     */
    public function verify2faCode(int $userId, string $code): bool
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            // Or throw an exception, depending on desired error handling
            return false; 
        }

        $user2faCode = $this->user2faCodeRepository->findValidCodeForUser($user, $code);
        
        if ($user2faCode) {
            $user->setLastLogin(new \DateTimeImmutable());
            $user2faCode->setIsUsed(true);

            $this->entityManager->persist($user);
            $this->entityManager->persist($user2faCode);
            $this->entityManager->flush();
            
            return true;
        }
        
        return false;
    }
}
