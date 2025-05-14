<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class LoginService
{
    private Connection $db;
    private EntityManagerInterface $entityManager;

    public function __construct(Connection $db, EntityManagerInterface $entityManager)
    {
        $this->db = $db;
        $this->entityManager = $entityManager;
    }

    /**
     * Validate user credentials (username/email and password).
     * Returns user array, '2fa' if two-factor is required, or false if invalid.
     */
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
        // Implement your 2FA code generation logic here (e.g., insert into users_2fa_codes, send email)
        // This should be handled in a dedicated service in production for separation of concerns.
    }

    private function updateTimestamp(int $userId): void
    {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $this->db->executeStatement($sql, ['id' => $userId]);
    }
}
