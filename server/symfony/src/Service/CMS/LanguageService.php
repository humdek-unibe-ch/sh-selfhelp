<?php

namespace App\Service\CMS;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Service\Core\BaseService;
use App\Util\EntityUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LanguageService extends BaseService
{
    private LanguageRepository $languageRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        LanguageRepository $languageRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->languageRepository = $languageRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Get all languages with ID > 1
     * 
     * @return array
     */
    public function getAllLanguages(): array
    {
        $languages = $this->languageRepository->findAllLanguages();
        return array_map(function (Language $language) {
            return EntityUtil::convertEntityToArray($language);
        }, $languages);
    }

    /**
     * Get all languages except the default one (ID = 1)
     * 
     * @return array
     */
    public function getAllNonInternalLanguages(): array
    {
        $languages = $this->languageRepository->findAllExceptInternal();
        return array_map(function (Language $language) {
            return EntityUtil::convertEntityToArray($language);
        }, $languages);
    }

    /**
     * Get a language by ID
     * 
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function getLanguageById(int $id): array
    {
        $language = $this->languageRepository->find($id);
        if (!$language) {
            throw new NotFoundHttpException('Language not found');
        }
        
        return EntityUtil::convertEntityToArray($language);
    }

    /**
     * Create a new language
     * 
     * @param array $data
     * @return array
     */
    public function createLanguage(array $data): array
    {
        $this->validateLanguageData($data);
        
        $language = new Language();
        $language->setLocale($data['locale']);
        $language->setLanguage($data['language']);
        
        if (isset($data['csv_separator'])) {
            $language->setCsvSeparator($data['csv_separator']);
        }
        
        $this->entityManager->persist($language);
        $this->entityManager->flush();
        
        return EntityUtil::convertEntityToArray($language);
    }

    /**
     * Update an existing language
     * 
     * @param int $id
     * @param array $data
     * @return array
     * @throws NotFoundHttpException
     */
    public function updateLanguage(int $id, array $data): array
    {
        $language = $this->languageRepository->find($id);
        if (!$language) {
            throw new NotFoundHttpException('Language not found');
        }
        
        // Cannot update language with ID = 1
        if ($id === 1) {
            throw new BadRequestHttpException('Cannot update the default language');
        }
        
        $this->validateLanguageData($data);
        
        if (isset($data['locale'])) {
            $language->setLocale($data['locale']);
        }
        
        if (isset($data['language'])) {
            $language->setLanguage($data['language']);
        }
        
        if (isset($data['csv_separator'])) {
            $language->setCsvSeparator($data['csv_separator']);
        }
        
        $this->entityManager->flush();
        
        return EntityUtil::convertEntityToArray($language);
    }

    /**
     * Delete a language
     * 
     * @param int $id
     * @return bool
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function deleteLanguage(int $id): bool
    {
        $language = $this->languageRepository->find($id);
        if (!$language) {
            throw new NotFoundHttpException('Language not found');
        }
        
        // Cannot delete language with ID = 1
        if ($id === 1) {
            throw new BadRequestHttpException('Cannot delete the default language');
        }
        
        $this->entityManager->remove($language);
        $this->entityManager->flush();
        
        return true;
    }

    /**
     * Validate language data
     * 
     * @param array $data
     * @throws BadRequestHttpException
     */
    private function validateLanguageData(array $data): void
    {
        if (!isset($data['locale']) || empty($data['locale'])) {
            throw new BadRequestHttpException('Locale is required');
        }
        
        if (!isset($data['language']) || empty($data['language'])) {
            throw new BadRequestHttpException('Language name is required');
        }
        
        if (isset($data['csv_separator']) && strlen($data['csv_separator']) !== 1) {
            throw new BadRequestHttpException('CSV separator must be a single character');
        }
    }
}
