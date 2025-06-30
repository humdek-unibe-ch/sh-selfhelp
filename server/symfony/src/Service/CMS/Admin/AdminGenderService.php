<?php

namespace App\Service\CMS\Admin;

use App\Entity\Gender;
use App\Repository\GenderRepository;
use App\Service\Core\BaseService;

class AdminGenderService extends BaseService
{
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
        $genders = $this->genderRepository->findAllGenders();
        
        return array_map(function (Gender $gender) {
            return [
                'id' => $gender->getId(),
                'name' => $gender->getName()
            ];
        }, $genders);
    }
} 