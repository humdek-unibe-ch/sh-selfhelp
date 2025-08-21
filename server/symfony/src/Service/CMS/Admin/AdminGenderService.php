<?php

namespace App\Service\CMS\Admin;

use App\Entity\Gender;
use App\Repository\GenderRepository;
use App\Service\Core\BaseService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Service\Cache\Core\ReworkedCacheService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminGenderService extends BaseService
{
    public function __construct(
        private readonly GenderRepository $genderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionService $transactionService,
        private readonly ReworkedCacheService $cache,
    ) {
    }

    /**
     * Get all genders with caching
     * 
     * @return array
     */
    public function getAllGenders(): array
    {
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_LOOKUPS)
            ->getList(
                'genders_all',
                function() {
                    $genders = $this->genderRepository->findAllGenders();
                    
                    return array_map(function (Gender $gender) {
                        return [
                            'id' => $gender->getId(),
                            'name' => $gender->getName()
                        ];
                    }, $genders);
                }
            );
    }

    /**
     * Get gender by ID with entity scope caching
     * 
     * @param int $genderId
     * @return array
     * @throws ServiceException
     */
    public function getGenderById(int $genderId): array
    {
        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_LOOKUPS)
            ->getItem(
                "gender_{$genderId}",
                function() use ($genderId) {
                    $gender = $this->genderRepository->find($genderId);
                    if (!$gender) {
                        throw new ServiceException('Gender not found', Response::HTTP_NOT_FOUND);
                    }
                    
                    return [
                        'id' => $gender->getId(),
                        'name' => $gender->getName()
                    ];
                }
            );
    }

    /**
     * Create new gender
     * 
     * @param array $genderData
     * @return array
     * @throws ServiceException
     */
    public function createGender(array $genderData): array
    {
        $this->entityManager->beginTransaction();

        try {
            $this->validateGenderData($genderData);

            $gender = new Gender();
            $gender->setName($genderData['name']);

            $this->entityManager->persist($gender);
            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_INSERT,
                LookupService::TRANSACTION_BY_BY_USER,
                'genders',
                $gender->getId(),
                $gender,
                'Gender created: ' . $gender->getName()
            );

            $this->entityManager->commit();

            // Invalidate cache after create (CREATE = lists only)
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_LOOKUPS)
                ->invalidateAllListsInCategory();

            return [
                'id' => $gender->getId(),
                'name' => $gender->getName()
            ];
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Update existing gender
     * 
     * @param int $genderId
     * @param array $genderData
     * @return array
     * @throws ServiceException
     */
    public function updateGender(int $genderId, array $genderData): array
    {
        $this->entityManager->beginTransaction();

        try {
            $gender = $this->genderRepository->find($genderId);
            if (!$gender) {
                throw new ServiceException('Gender not found', Response::HTTP_NOT_FOUND);
            }

            $this->validateGenderData($genderData);

            if (isset($genderData['name'])) {
                $gender->setName($genderData['name']);
            }

            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'genders',
                $gender->getId(),
                $gender,
                'Gender updated: ' . $gender->getName()
            );

            $this->entityManager->commit();

            // UPDATE = entity scope + lists (no entity scope for genders, so just lists)
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_LOOKUPS)
                ->invalidateItemAndLists("gender_{$genderId}");

            return [
                'id' => $gender->getId(),
                'name' => $gender->getName()
            ];
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Delete gender
     * 
     * @param int $genderId
     * @return bool
     * @throws ServiceException
     */
    public function deleteGender(int $genderId): bool
    {
        $this->entityManager->beginTransaction();

        try {
            $gender = $this->genderRepository->find($genderId);
            if (!$gender) {
                throw new ServiceException('Gender not found', Response::HTTP_NOT_FOUND);
            }

            // Log transaction before deletion
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'genders',
                $gender->getId(),
                $gender,
                'Gender deleted: ' . $gender->getName()
            );

            $this->entityManager->remove($gender);
            $this->entityManager->flush();

            $this->entityManager->commit();

            // DELETE = entity scope + lists (no entity scope for genders, so just lists)
            $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_LOOKUPS)
                ->invalidateItemAndLists("gender_{$genderId}");

            return true;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Validate gender data
     * 
     * @param array $data
     * @throws ServiceException
     */
    private function validateGenderData(array $data): void
    {
        if (!isset($data['name']) || empty(trim($data['name']))) {
            throw new ServiceException('Gender name is required', Response::HTTP_BAD_REQUEST);
        }

        // Check for duplicate name
        $existingGender = $this->genderRepository->findOneBy(['name' => trim($data['name'])]);
        if ($existingGender) {
            throw new ServiceException('Gender name already exists', Response::HTTP_CONFLICT);
        }
    }
} 