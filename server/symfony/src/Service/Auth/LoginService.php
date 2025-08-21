<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\AuthRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginService
{
    private Connection $db;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private AuthRepository $authRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        Connection $db,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        AuthRepository $authRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->db = $db;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->authRepository = $authRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Validate user credentials (username/email and password).
     * Returns user array, '2fa' (as string) if two-factor is required, or null if invalid.
     *
     * @param string $user
     * @param string $password
     * @return bool|User|string
     */
    public function validateUser(string $user, string $password): bool|User|string
    {
        // Find the user by email using UserRepository
        $userEntity = $this->userRepository->findOneBy(['email' => $user]);
        if (!$userEntity || !$this->passwordHasher->isPasswordValid($userEntity, $password)) {
            return false;
        }        
        return $userEntity;
    }

}
