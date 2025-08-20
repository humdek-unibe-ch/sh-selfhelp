<?php

namespace App\Service\CMS\Frontend;

use App\Entity\CmsPreference;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Repository\SectionsFieldsTranslationRepository;
use App\Repository\StylesFieldRepository;
use App\Repository\PagesFieldsTranslationRepository;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Cache\Core\ReworkedCacheService;
use App\Service\Core\LookupService;
use App\Service\CMS\Common\SectionUtilityService;
use App\Service\Core\BaseService;
use App\Service\Core\UserContextAwareService;
use Doctrine\ORM\EntityManagerInterface;

class PageService extends BaseService
{
    // Default values for language
    private const PROPERTY_LANGUAGE_ID = 1; // Language ID 1 is for properties, not a real language

    public function __construct(
        private readonly SectionRepository $sectionRepository,
        private readonly LookupService $lookupService,
        private readonly ACLService $aclService,
        private readonly PageRepository $pageRepository,
        private readonly SectionsFieldsTranslationRepository $translationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly StylesFieldRepository $stylesFieldRepository,
        private readonly SectionUtilityService $sectionUtilityService,
        private readonly PagesFieldsTranslationRepository $pagesFieldsTranslationRepository,
        private readonly ReworkedCacheService $cache,
        private readonly UserContextAwareService $userContextAwareService
    ) {
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
     * @param int|null $language_id Optional language ID for translations
     * @return array
     */
    public function getAllAccessiblePagesForUser(string $mode, bool $admin, ?int $language_id = null): array
    {
        $user = $this->userContextAwareService->getCurrentUser();
        $userId = 1; // guest user
        if ($user) {
            $userId = $user->getId();
        }

        // Determine which language ID to use for translations
        $languageId = $this->determineLanguageId($language_id);

        // Try to get from cache first
        $cacheKey = "pages_{$mode}_{$admin}_{$languageId}";

        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
            ->withUser($userId)
            ->getItem($cacheKey, function () use ($mode, $admin, $languageId, $userId) {
                // Get all pages with ACL for the user using the ACLService (cached)
                $allPages = $this->aclService->getAllUserAcls($userId);

                // Determine which type to remove based on mode
                $removeType = $mode === LookupService::PAGE_ACCESS_TYPES_MOBILE ? LookupService::PAGE_ACCESS_TYPES_WEB : LookupService::PAGE_ACCESS_TYPES_MOBILE;
                $removeTypeId = $this->lookupService->getLookupIdByCode(LookupService::PAGE_ACCESS_TYPES, $removeType);

                // If mode is both, do not remove any type
                $filteredPages = array_values(array_filter($allPages, function ($item) use ($removeTypeId, $mode, $admin) {

                    // Base ACL check
                    if ($item['acl_select'] != 1) {
                        return false;
                    }

                    // If admin is true, then all pages (normal filtering)
                    // If not admin, then only pages with id_type = 2 or 3 (core and experiment pages)
                    if (!$admin && isset($item['id_type']) && !in_array($item['id_type'], [2, 3])) {
                        return false;
                    }

                    // Apply mode-based filtering
                    if ($mode === LookupService::PAGE_ACCESS_TYPES_MOBILE_AND_WEB) {
                        return true;
                    }

                    return $item['id_pageAccessTypes'] != $removeTypeId;
                }));

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

                // Extract page IDs for fetching translations
                $pageIds = array_column($filteredPages, 'id_pages');

                // Fetch all page title translations in one query
                $pageTitleTranslations = [];
                if (!empty($pageIds)) {
                    $pageTitleTranslations = $this->pagesFieldsTranslationRepository->fetchTitleTranslationsWithFallback(
                        $pageIds,
                        $languageId,
                        $defaultLanguageId
                    );
                }

                // Create a map of pages by their ID for quick lookup
                $pagesMap = [];
                foreach ($filteredPages as &$page) {

                    // Add title translations to page
                    $pageId = $page['id_pages'];
                    $page['title'] = null; // Default title
                    if (isset($pageTitleTranslations[$pageId])) {
                        // Look for a 'title' field first, otherwise take the first available field
                        if (isset($pageTitleTranslations[$pageId]['title'])) {
                            $page['title'] = $pageTitleTranslations[$pageId]['title'];
                        } else {
                            // Take the first available translation field as title
                            $page['title'] = reset($pageTitleTranslations[$pageId]) ?: null;
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

                // Cache the result for this user
                return $nestedPages;
            });



    }

    /**
     * Get page by keyword with translated sections
     * 
     * @param string $page_keyword The page keyword
     * @param int|null $language_id Optional language ID for translations
     * @return array The page object with translated sections
     * @throws \App\Exception\ServiceException If page not found or access denied
     */
    public function getPage(string $page_keyword, ?int $language_id = null): array
    {
        // Determine which language ID to use for translations
        $languageId = $this->determineLanguageId($language_id);

        // Get current user for caching
        $user = $this->userContextAwareService->getCurrentUser();
        $userId = $user ? $user->getId() : 1; // guest user

        // Try to get from cache first
        $cacheKey = "page_{$page_keyword}_{$languageId}";

        return $this->cache
            ->withCategory(ReworkedCacheService::CATEGORY_PAGES)
            ->withUser($userId)
            ->getItem($cacheKey, function () use ($page_keyword, $languageId) {
                $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
                if (!$page) {
                    $this->throwNotFound('Page not found');
                }

                // Check if user has access to the page
                $this->userContextAwareService->checkAccess($page_keyword, 'select');

                $pageData = [
                    'page' => [
                        'id' => $page->getId(),
                        'keyword' => $page->getKeyword(),
                        'url' => $page->getUrl(),
                        'parent_page_id' => $page->getParentPage()?->getId(),
                        'is_headless' => $page->isHeadless(),
                        'nav_position' => $page->getNavPosition(),
                        'footer_position' => $page->getFooterPosition(),
                        'sections' => $this->getPageSections($page->getId(), $languageId)
                    ]
                ];

                return $pageData;
            });
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
        $sections = $this->sectionUtilityService->buildNestedSections($flatSections, true);

        // Extract all section IDs from the hierarchical structure
        $sectionIds = $this->sectionUtilityService->extractSectionIds($sections);

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
            $languageId
        );

        // If requested language is not the default language, fetch default language translations for fallback
        $defaultTranslations = [];
        if ($defaultLanguageId !== null && $languageId !== $defaultLanguageId) {
            $defaultTranslations = $this->translationRepository->fetchTranslationsForSections(
                $sectionIds,
                $defaultLanguageId
            );
        }

        // Fetch property translations (language ID 1) for fields of type 1
        $propertyTranslations = $this->translationRepository->fetchTranslationsForSections(
            $sectionIds,
            self::PROPERTY_LANGUAGE_ID
        );

        // Apply translations to the sections recursively with fallback
        $this->sectionUtilityService->applySectionTranslations($sections, $translations, $defaultTranslations, $propertyTranslations);

        $this->sectionUtilityService->applySectionsData($sections);

        return $sections;
    }

    /**
     * Determine which language ID to use for translations
     * 
     * @param int|null $language_id Explicitly provided language ID
     * @return int The language ID to use
     */
    private function determineLanguageId(?int $language_id = null): int
    {
        // If language_id is explicitly provided, use it
        if ($language_id !== null) {
            return $language_id;
        }

        // If user is logged in, use their preferred language
        $user = $this->userContextAwareService->getCurrentUser();
        if ($user && $user->getLanguage()) {
            return $user->getLanguage()->getId();
        }

        // Otherwise use default language from CMS preferences
        try {
            $cmsPreference = $this->cache
                ->withCategory(ReworkedCacheService::CATEGORY_CMS_PREFERENCES)
                ->getItem("cms_preferences", fn() => $this->entityManager->getRepository(CmsPreference::class)->findOneBy([]));
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
