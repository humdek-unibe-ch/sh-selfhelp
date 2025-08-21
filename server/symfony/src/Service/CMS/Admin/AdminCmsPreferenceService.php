<?php

namespace App\Service\CMS\Admin;

use App\Repository\CmsPreferenceRepository;
use App\Repository\LanguageRepository;
use App\Service\Core\BaseService;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Service\Cache\Core\CacheService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminCmsPreferenceService extends BaseService
{
    public function __construct(
        private readonly CmsPreferenceRepository $cmsPreferenceRepository,
        private readonly LanguageRepository $languageRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionService $transactionService,
        private readonly CacheService $cache,
    ) {
    }

    /**
     * Get CMS preferences with entity scope caching
     * 
     * @return array
     */
    public function getCmsPreferences(): array
    {
        return $this->cache
            ->withCategory(CacheService::CATEGORY_CMS_PREFERENCES)
            ->getItem(
                'cms_preferences',
                function() {
                    $preferences = $this->cmsPreferenceRepository->getCmsPreferences();
                    
                    if (!$preferences) {
                        throw new ServiceException('CMS preferences not found', Response::HTTP_NOT_FOUND);
                    }

                    // Add entity scope for the CMS preference instance
                    $this->cache
                        ->withCategory(CacheService::CATEGORY_CMS_PREFERENCES)
                        ->withEntityScope(CacheService::ENTITY_SCOPE_CMS_PREFERENCE, $preferences->getId())
                        ->setItem('cms_preferences_scoped', null, [
                            'id' => $preferences->getId(),
                            'callback_api_key' => $preferences->getCallbackApiKey(),
                            'default_language_id' => $preferences->getDefaultLanguage()?->getId(),
                            'default_language' => $preferences->getDefaultLanguage() ? [
                                'id' => $preferences->getDefaultLanguage()->getId(),
                                'locale' => $preferences->getDefaultLanguage()->getLocale(),
                                'language' => $preferences->getDefaultLanguage()->getLanguage()
                            ] : null,
                            'anonymous_users' => $preferences->getAnonymousUsers(),
                            'firebase_config' => $preferences->getFirebaseConfig()
                        ]);

                    return [
                        'id' => $preferences->getId(),
                        'callback_api_key' => $preferences->getCallbackApiKey(),
                        'default_language_id' => $preferences->getDefaultLanguage()?->getId(),
                        'default_language' => $preferences->getDefaultLanguage() ? [
                            'id' => $preferences->getDefaultLanguage()->getId(),
                            'locale' => $preferences->getDefaultLanguage()->getLocale(),
                            'language' => $preferences->getDefaultLanguage()->getLanguage()
                        ] : null,
                        'anonymous_users' => $preferences->getAnonymousUsers(),
                        'firebase_config' => $preferences->getFirebaseConfig()
                    ];
                }
            );
    }

    /**
     * Update CMS preferences
     * 
     * @param array $data
     * @return array
     */
    public function updateCmsPreferences(array $data): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            $preferences = $this->cmsPreferenceRepository->getCmsPreferences();
            
            if (!$preferences) {
                throw new ServiceException('CMS preferences not found', Response::HTTP_NOT_FOUND);
            }

            // Update callback API key if provided
            if (array_key_exists('callback_api_key', $data)) {
                $preferences->setCallbackApiKey($data['callback_api_key']);
            }

            // Update default language if provided
            if (array_key_exists('default_language_id', $data)) {
                if ($data['default_language_id'] === null) {
                    $preferences->setDefaultLanguage(null);
                } else {
                    $language = $this->languageRepository->find($data['default_language_id']);
                    if (!$language) {
                        throw new ServiceException('Language not found', Response::HTTP_BAD_REQUEST);
                    }
                    $preferences->setDefaultLanguage($language);
                }
            }

            // Update anonymous users if provided
            if (array_key_exists('anonymous_users', $data)) {
                $preferences->setAnonymousUsers((int)$data['anonymous_users']);
            }

            // Update firebase config if provided
            if (array_key_exists('firebase_config', $data)) {
                $preferences->setFirebaseConfig($data['firebase_config']);
            }

            $this->entityManager->flush();

            // Log transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'cms_preferences',
                $preferences->getId(),
                $preferences,
                'CMS preferences updated'
            );

            $this->entityManager->commit();

            // Invalidate entity-scoped cache for CMS preferences
            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_CMS_PREFERENCE, $preferences->getId());
            $this->cache
                ->withCategory(CacheService::CATEGORY_CMS_PREFERENCES)
                ->invalidateAllListsInCategory();

            return $this->getCmsPreferences();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
} 