<?php

namespace App\Service\CMS;

use App\Entity\CmsPreference;
use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Service\Core\BaseService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Util\EntityUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class LanguageService extends BaseService
{
    private LanguageRepository $languageRepository;
    private EntityManagerInterface $entityManager;
    private TransactionService $transactionService;

    public function __construct(
        LanguageRepository $languageRepository,
        EntityManagerInterface $entityManager,
        TransactionService $transactionService
    ) {
        $this->languageRepository = $languageRepository;
        $this->entityManager = $entityManager;
        $this->transactionService = $transactionService;
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
     * Get all languages except the internal one (ID = 1)
     * Always returns the default language first, followed by other languages
     * 
     * @return array
     */
    public function getAllNonInternalLanguages(): array
    {
        // Clear any cached entities to avoid proxy objects
        $this->entityManager->clear();
        
        // Get all non-internal languages
        $languages = $this->languageRepository->findAllExceptInternal();
        
        // Get default language from CMS preferences
        $defaultLanguage = null;
        $defaultLanguageId = null;
        
        try {
            $cmsPreference = $this->entityManager->getRepository(CmsPreference::class)->findOneBy([]);
            if ($cmsPreference && $cmsPreference->getDefaultLanguage()) {
                $defaultLanguage = $cmsPreference->getDefaultLanguage();
                $defaultLanguageId = $defaultLanguage->getId();
            }
        } catch (\Exception $e) {
            // If there's an error getting the default language, continue without it
        }
        
        // Convert entities to arrays
        $languageArrays = array_map(function (Language $language) {
            return EntityUtil::convertEntityToArray($language);
        }, $languages);
        
        // If we have a default language, ensure it's first in the array
        if ($defaultLanguageId !== null) {
            $defaultLanguageArray = null;
            $otherLanguages = [];
            
            foreach ($languageArrays as $langArray) {
                if ($langArray['id'] === $defaultLanguageId) {
                    $defaultLanguageArray = $langArray;
                } else {
                    $otherLanguages[] = $langArray;
                }
            }
            
            // If default language was found in the results, put it first
            if ($defaultLanguageArray !== null) {
                return array_merge([$defaultLanguageArray], $otherLanguages);
            }
        }
        
        return $languageArrays;
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
        $this->entityManager->beginTransaction();
        
        try {
            $this->validateLanguageData($data);
            
            $language = new Language();
            $language->setLocale($data['locale']);
            $language->setLanguage($data['language']);
            
            if (isset($data['csv_separator'])) {
                $language->setCsvSeparator($data['csv_separator']);
            }
            
            $this->entityManager->persist($language);
            $this->entityManager->flush();
            
            // Log the transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_INSERT,
                LookupService::TRANSACTION_BY_BY_USER,
                'language',
                $language->getId(),
                $language,
                'Language created: ' . $language->getLanguage() . ' (' . $language->getLocale() . ')'
            );
            
            $this->entityManager->commit();
            return EntityUtil::convertEntityToArray($language);
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
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
        $this->entityManager->beginTransaction();
        
        try {
            $language = $this->languageRepository->find($id);
            if (!$language) {
                throw new NotFoundHttpException('Language not found');
            }
            
            // Cannot update language with ID = 1
            if ($id === 1) {
                throw new BadRequestHttpException('Cannot update the default language');
            }
            
            // Store original values for logging
            $originalLanguage = clone $language;
            
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
            
            // Log the transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'language',
                $language->getId(),
                $language, // Pass the language object directly
                'Language updated: ' . $language->getLanguage() . ' (' . $language->getLocale() . ') [Original: ' . $originalLanguage->getLanguage() . ' (' . $originalLanguage->getLocale() . ')]'
            );
            
            $this->entityManager->commit();
            return EntityUtil::convertEntityToArray($language);
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Delete a language
     * 
     * @param int $id
     * @return Language
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function deleteLanguage(int $id): Language
    {
        $this->entityManager->beginTransaction();
        
        try {
            $language = $this->languageRepository->find($id);
            if (!$language) {
                throw new NotFoundHttpException('Language not found');
            }
            
            // Cannot delete language with ID = 1
            if ($id === 1) {
                throw new BadRequestHttpException('Cannot delete the default language');
            }
            
            // Store language data for logging before deletion
            $deletedLanguage = clone $language;
            $languageLocale = $language->getLocale();
            $languageName = $language->getLanguage();
            $languageId = $language->getId();
            
            $this->entityManager->remove($language);
            $this->entityManager->flush();
            
            // Log the transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_DELETE,
                LookupService::TRANSACTION_BY_BY_USER,
                'language',
                $languageId,
                $deletedLanguage,
                'Language deleted: ' . $languageName . ' (' . $languageLocale . ')'
            );
            
            $this->entityManager->commit();
            return $deletedLanguage;
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
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
