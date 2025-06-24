<?php

namespace App\Service\CMS\Frontend;

use App\Entity\Page;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Repository\SectionsFieldsTranslationRepository;
use App\Repository\StylesFieldRepository;
use App\Service\ACLService;
use App\Service\Core\ServiceException;
use App\Service\Core\UserContextAwareService;
use App\Service\UserContextService;
use App\Repository\LookupRepository;
use App\Service\ACL\ACLService as ACLACLService;
use App\Service\Auth\UserContextService as AuthUserContextService;
use App\Service\Core\LookupService;
use App\Service\CMS\Common\SectionUtilityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class PageService extends UserContextAwareService
{
    // Default values for language and gender
    private const PROPERTY_LANGUAGE_ID = 1; // Language ID 1 is for properties, not a real language
    private const DEFAULT_GENDER_ID = 1;   // Assuming 1 is the default gender ID

    public function __construct(
        SectionRepository $sectionRepository,
        private readonly LookupRepository $lookupRepository,
        AuthUserContextService $userContextService,
        ACLACLService $aclService,
        PageRepository $pageRepository,
        private readonly SectionsFieldsTranslationRepository $translationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly StylesFieldRepository $stylesFieldRepository,
        private readonly SectionUtilityService $sectionUtilityService
    ) {
        parent::__construct($userContextService, $aclService, $pageRepository, $sectionRepository);
    }

    /**
     * Recursively sorts pages by nav_position
     * Pages with null nav_position will be placed at the end and sorted alphabetically by keyword
     */
    private function sortPagesRecursively(array &$pages): void
    {
        usort($pages, function ($a, $b) {
            // If both positions are null, sort alphabetically by keyword
            if ($a['nav_position'] === null && $b['nav_position'] === null) {
                return strcasecmp($a['keyword'] ?? '', $b['keyword'] ?? '');
            }

            // If only a's position is null, it should go after b
            if ($a['nav_position'] === null) {
                return 1;
            }

            // If only b's position is null, it should go after a
            if ($b['nav_position'] === null) {
                return -1;
            }

            // If both have positions, compare them normally
            return $a['nav_position'] <=> $b['nav_position'];
        });

        foreach ($pages as &$page) {
            if (!empty($page['children'])) {
                $this->sortPagesRecursively($page['children']);
            }
        }
    }

    /**
     * Get all published pages for the current user, filtered by mode and ACL
     *
     * @param string $mode Either 'web' or 'mobile'
     * @return array
     */
    public function getAllAccessiblePagesForUser(string $mode): array
    {
        $user = $this->getCurrentUser();
        $userId = 1; // guest user
        if ($user) {
            $userId = $user->getId();
        }

        // Get all pages with ACL for the user using the ACLService (cached)
        $allPages = $this->aclService->getAllUserAcls($userId);

        // Determine which type to remove based on mode
        $removeType = $mode === LookupService::PAGE_ACCESS_TYPES_MOBILE ? LookupService::PAGE_ACCESS_TYPES_WEB : LookupService::PAGE_ACCESS_TYPES_MOBILE;
        $removeTypeId = $this->lookupRepository->getLookupIdByCode(LookupService::PAGE_ACCESS_TYPES, $removeType);
        $sectionsTypeId = $this->lookupRepository->getLookupIdByCode(LookupService::PAGE_ACTIONS, LookupService::PAGE_ACTIONS_SECTIONS);

        // If mode is both, do not remove any type
        $filteredPages = array_values(array_filter($allPages, function ($item) use ($removeTypeId, $sectionsTypeId, $mode) {
            // TODO: Adjust the filtering once the structure is adjusted
            if ($mode === LookupService::PAGE_ACCESS_TYPES_MOBILE_AND_WEB) {
                return $item['acl_select'] == 1 &&
                    $item['id_actions'] == $sectionsTypeId &&
                    in_array($item['id_type'], ['2', '3', '4']) &&
                    $item['url'] != '';
            }
            return $item['id_pageAccessTypes'] != $removeTypeId &&
                $item['acl_select'] == 1 &&
                $item['id_actions'] == $sectionsTypeId &&
                in_array($item['id_type'], ['2', '3', '4']) &&
                $item['url'] != '';
        }));

        // Create a map of pages by their ID for quick lookup
        $pagesMap = [];
        foreach ($filteredPages as &$page) {
            // Set default protocol if missing
            if (!isset($page['protocol']) || $page['protocol'] === null) {
                // Extract protocol from URL if possible, otherwise default to https
                if (!empty($page['url']) && strpos($page['url'], '://') !== false) {
                    $parts = parse_url($page['url']);
                    $page['protocol'] = $parts['scheme'] ?? 'https';
                } else {
                    $page['protocol'] = 'https';
                }
            }

            $page['children'] = []; // Initialize children array
            $pagesMap[$page['id_pages']] = &$page;
        }
        unset($page); // Break the reference

        // Build the hierarchy
        $nestedPages = [];
        foreach ($pagesMap as $id => &$page) {
            if (isset($page['parent']) && $page['parent'] !== null && isset($pagesMap[$page['parent']])) {
                // This is a child page, add it to its parent's children array
                $pagesMap[$page['parent']]['children'][] = &$page;
            } else {
                // This is a root level page
                $nestedPages[] = &$page;
            }
        }
        unset($page); // Break the reference

        // Optional: Sort children by nav_position if needed
        $this->sortPagesRecursively($nestedPages);

        return $nestedPages;
    }

    /**
     * Get page by keyword with translated sections
     * 
     * @param string $page_keyword The page keyword
     * @param string|null $locale Optional locale for translations (e.g. 'en', 'de')
     * @return array The page object with translated sections
     * @throws ServiceException If page not found or access denied
     */
    public function getPage(string $page_keyword, ?string $locale = null): array
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check if user has access to the page
        $this->checkAccess($page_keyword, 'select');
        
        // Determine which language ID to use for translations
        $languageId = $this->determineLanguageId($locale);

        return [
            'id' => $page->getId(),
            'keyword' => $page->getKeyword(),
            'url' => $page->getUrl(),
            'parent_page_id' => $page->getParentPage()?->getId(),
            'is_headless' => $page->isHeadless(),
            'nav_position' => $page->getNavPosition(),
            'footer_position' => $page->getFooterPosition(),
            'sections' => $this->getPageSections($page->getId(), $languageId)
        ];
    }

    /**
     * Get page sections with translations
     * 
     * @param int $page_id The page ID
     * @param int $languageId The language ID for translations
     * @return array The page sections in a hierarchical structure with translations
     */
    public function getPageSections(int $page_id, int $languageId): array
    {
        // Get flat sections with hierarchical information
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page_id);
        
        // Build nested hierarchical structure
        $sections = $this->sectionUtilityService->buildNestedSections($flatSections);
        
        // Extract all section IDs from the hierarchical structure
        $sectionIds = $this->extractSectionIds($sections);
        
        // Get default language ID for fallback translations
        $defaultLanguageId = null;
        try {
            $cmsPreference = $this->entityManager->getRepository('App\\Entity\\CmsPreference')->findOneBy([]);
            if ($cmsPreference && $cmsPreference->getDefaultLanguage()) {
                $defaultLanguageId = $cmsPreference->getDefaultLanguage()->getId();
            }
        } catch (\Exception $e) {
            // If there's an error getting the default language, continue without fallback
        }
        
        // Fetch all translations for these sections in one query
        $translations = $this->translationRepository->fetchTranslationsForSections(
            $sectionIds,
            $languageId,
            self::DEFAULT_GENDER_ID
        );
        
        // If requested language is not the default language, fetch default language translations for fallback
        $defaultTranslations = [];
        if ($defaultLanguageId !== null && $languageId !== $defaultLanguageId) {
            $defaultTranslations = $this->translationRepository->fetchTranslationsForSections(
                $sectionIds,
                $defaultLanguageId,
                self::DEFAULT_GENDER_ID
            );
        }
        
        // Fetch property translations (language ID 1) for fields of type 1
        $propertyTranslations = $this->translationRepository->fetchTranslationsForSections(
            $sectionIds,
            self::PROPERTY_LANGUAGE_ID,
            self::DEFAULT_GENDER_ID
        );
        
        // Apply translations to the sections recursively with fallback
        $this->applySectionTranslations($sections, $translations, $defaultTranslations, $propertyTranslations);
        
        return $sections;
    }
    
    /**
     * Recursively extract all section IDs from a hierarchical sections structure
     * 
     * @param array $sections Hierarchical sections structure
     * @return array Flat array of section IDs
     */
    private function extractSectionIds(array $sections): array
    {
        $ids = [];
        
        foreach ($sections as $section) {
            if (isset($section['id'])) {
                $ids[] = $section['id'];
            }
            
            // Process children recursively
            if (!empty($section['children'])) {
                $childIds = $this->extractSectionIds($section['children']);
                $ids = array_merge($ids, $childIds);
            }
        }
        
        return $ids;
    }
    
    /**
     * Apply translations to sections recursively
     * 
     * @param array &$sections The sections to apply translations to (passed by reference)
     * @param array $translations The translations keyed by section ID
     * @param array $defaultTranslations Default language translations for fallback
     * @param array $propertyTranslations Property translations (language ID 1) for fields of type 1
     */
    private function applySectionTranslations(
        array &$sections, 
        array $translations, 
        array $defaultTranslations = [], 
        array $propertyTranslations = []
    ): void {
        foreach ($sections as &$section) {
            $sectionId = $section['id'] ?? null;
            
            if ($sectionId) {
                // Get the section's style ID to fetch default values if needed
                $styleId = $section['id_styles'] ?? null;
                
                // First apply property translations (for fields of type 1)
                if (isset($propertyTranslations[$sectionId])) {
                    $section = array_merge($section, $propertyTranslations[$sectionId]);
                }
                
                // Then apply default language translations as fallback
                if (isset($defaultTranslations[$sectionId])) {
                    $section = array_merge($section, $defaultTranslations[$sectionId]);
                }
                
                // Finally apply requested language translations (overriding any fallbacks)
                if (isset($translations[$sectionId])) {
                    $section = array_merge($section, $translations[$sectionId]);
                }
                
                // For any fields that still don't have values, use default values from styles_fields table
                if ($styleId) {
                    // Get all fields for this section's style
                    $stylesFields = $this->stylesFieldRepository->findDefaultValuesByStyleId($styleId);
                    $fields = [];
                    
                    // Apply default values for fields that don't have translations
                    foreach ($stylesFields as $fieldName => $defaultValue) {
                        // Only apply default value if the field doesn't already have a value
                        if (!isset($section[$fieldName]) || empty($section[$fieldName]['content'])) {
                            $fields[$fieldName] = [
                                'content' => $defaultValue,
                                'meta' => null
                            ];
                        }
                    }
                }
            }
            $section['fields'] = $fields;
            
            // Process children recursively
            if (isset($section['children']) && is_array($section['children'])) {
                $this->applySectionTranslations(
                    $section['children'], 
                    $translations, 
                    $defaultTranslations, 
                    $propertyTranslations
                );
            }
        }
    }
    
    /**
     * Determine which language ID to use for translations
     * 
     * @param string|null $locale Explicitly provided locale (e.g. 'en', 'de')
     * @return int The language ID to use
     */
    private function determineLanguageId(?string $locale = null): int
    {
        // If locale is explicitly provided, find corresponding language ID
        if ($locale !== null) {
            $language = $this->entityManager->getRepository('App\\Entity\\Language')->findByLocale($locale);
            if ($language) {
                return $language->getId();
            }
        }
        
        // If user is logged in, use their preferred language
        $user = $this->getCurrentUser();
        if ($user && $user->getIdLanguages()) {
            return $user->getIdLanguages();
        }
        
        // Otherwise use default language from CMS preferences
        try {
            $cmsPreference = $this->entityManager->getRepository('App\\Entity\\CmsPreference')->findOneBy([]);
            if ($cmsPreference && $cmsPreference->getDefaultLanguage()) {
                return $cmsPreference->getDefaultLanguage()->getId();
            }
        } catch (\Exception $e) {
            // If there's an error getting the default language, use fallback
        }
        
        // Fallback to language ID 2 if no default language is configured
        return 2;
    }
}
