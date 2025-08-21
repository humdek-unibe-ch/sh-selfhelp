<?php

namespace App\Service\CMS\Admin;

use App\Entity\Gender;
use App\Repository\GenderRepository;
use App\Service\Core\BaseService;
use App\Service\Cache\Core\ReworkedCacheService;

class AdminGenderService extends BaseService
{
    public function __construct(
        private readonly GenderRepository $genderRepository,
        private readonly ReworkedCacheService $cache,
    ) {
    }

    /**
     * Get all genders with entity scope caching
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
} 