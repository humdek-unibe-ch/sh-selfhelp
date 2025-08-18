<?php

namespace App\Service\CMS\Admin;

use App\Entity\Gender;
use App\Service\Cache\Core\CacheableServiceTrait;
use App\Repository\GenderRepository;
use App\Service\Core\BaseService;
use App\Service\Cache\Core\CacheService;

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
        return $this->getCache(
            CacheService::CATEGORY_LOOKUPS,
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
null
        );
    }
} 