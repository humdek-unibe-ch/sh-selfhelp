<?php

namespace App\Service\CMS\Admin;

use App\Entity\Gender;
use App\Repository\GenderRepository;
use App\Service\Core\BaseService;
use App\Service\Core\CacheableServiceTrait;
use App\Service\Core\GlobalCacheService;

class AdminGenderService extends BaseService
{
    use CacheableServiceTrait;

    public function __construct(
        private readonly GenderRepository $genderRepository
    ) {
    }

    /**
     * Get all genders
     * 
     * @return array
     */
    public function getAllGenders(): array
    {
        return $this->cacheGet(
            GlobalCacheService::CATEGORY_LOOKUPS,
            'genders_all',
            function() {
                $genders = $this->genderRepository->findAllGenders();
                
                return array_map(function (Gender $gender) {
                    return [
                        'id' => $gender->getId(),
                        'name' => $gender->getName()
                    ];
                }, $genders);
            },
            $this->getCacheTTL(GlobalCacheService::CATEGORY_LOOKUPS)
        );
    }
} 