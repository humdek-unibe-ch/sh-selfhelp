<?php

namespace App\Service\CMS\Admin;

use App\Entity\CmsPreference;
use App\Entity\Language;
use App\Repository\CmsPreferenceRepository;
use App\Repository\LanguageRepository;
use App\Service\Core\BaseService;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminCmsPreferenceService extends BaseService
{
    public function __construct(
        private readonly CmsPreferenceRepository $cmsPreferenceRepository,
        private readonly LanguageRepository $languageRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Get CMS preferences
     * 
     * @return array
     */
    public function getCmsPreferences(): array
    {
        $preferences = $this->cmsPreferenceRepository->getCmsPreferences();
        
        if (!$preferences) {
            throw new ServiceException('CMS preferences not found', Response::HTTP_NOT_FOUND);
        }

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

    /**
     * Update CMS preferences
     * 
     * @param array $data
     * @return array
     */
    public function updateCmsPreferences(array $data): array
    {
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

        return $this->getCmsPreferences();
    }
} 