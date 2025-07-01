<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Users2faCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @extends ServiceEntityRepository<Users2faCode>
 *
 * @method Users2faCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users2faCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users2faCode[]    findAll()
 * @method Users2faCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class User2faCodeRepository extends ServiceEntityRepository
{
    private UserRepository $userRepository;

    public function __construct(ManagerRegistry $registry, UserRepository $userRepository)
    {
        parent::__construct($registry, Users2faCode::class);
        $this->userRepository = $userRepository;
    }

    /**
     * Inserts a new 2FA code for a user.
     */
    public function insert(int $userId, string $code, DateTimeInterface $expiresAt): void
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            // Or throw an exception, depending on how you want to handle missing users
            return;
        }

        $user2faCode = new Users2faCode();
        $user2faCode->setUser($user); // Set the User entity        
        $user2faCode->setCode($code);
        $user2faCode->setCreatedAt(new DateTime());
        $user2faCode->setExpiresAt($expiresAt);
        $user2faCode->setIsUsed(false);

        $this->getEntityManager()->persist($user2faCode);
        $this->getEntityManager()->flush();
    }

    /**
     * Finds a valid (non-expired, not used) 2FA code for a given user and code string.
     */
    public function findValidCodeForUser(User $user, string $code): ?Users2faCode
    {
        return $this->createQueryBuilder('u2c')
            ->andWhere('u2c.user = :user')
            ->andWhere('u2c.code = :code')
            ->andWhere('u2c.expiresAt > :now')
            ->andWhere('u2c.isUsed = :isUsed')
            ->setParameter('user', $user)
            ->setParameter('code', $code)
            ->setParameter('now', new DateTimeImmutable())
            ->setParameter('isUsed', false)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
