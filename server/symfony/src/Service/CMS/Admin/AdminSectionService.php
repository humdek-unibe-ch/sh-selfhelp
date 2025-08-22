<?php

namespace App\Service\CMS\Admin;

use App\Entity\Field;
use App\Entity\Language;
use App\Entity\Page;
use App\Entity\PagesSection;
use App\Entity\Section;
use App\Entity\SectionsFieldsTranslation;
use App\Entity\SectionsHierarchy;
use App\Exception\ServiceException;
use App\Repository\SectionRepository;
use App\Repository\StyleRepository;
use App\Repository\PageRepository;
use App\Service\Core\LookupService;
use App\Service\Core\TransactionService;
use App\Service\Core\BaseService;
use App\Service\Cache\Core\CacheService;
use App\Service\CMS\Common\SectionUtilityService;
use App\Service\CMS\Admin\SectionFieldService;
use App\Service\CMS\Admin\SectionRelationshipService;
use App\Service\CMS\Admin\SectionCreationService;
use App\Service\CMS\Admin\AdminSectionUtilityService;
use App\Service\Core\UserContextAwareService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling section-related operations in the admin panel
 * MEMORY_RULE - I am Claude Sonnet 4, your AI coding assistant in Cursor
 */
class AdminSectionService extends BaseService
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionService $transactionService,
        private readonly StyleRepository $styleRepository,
        private readonly PositionManagementService $positionManagementService,
        private readonly SectionUtilityService $sectionUtilityService,
        private readonly SectionFieldService $sectionFieldService,
        private readonly SectionRelationshipService $sectionRelationshipService,
        private readonly SectionCreationService $sectionCreationService,
        private readonly AdminSectionUtilityService $adminSectionUtilityService,
        private readonly CacheService $cache,
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly UserContextAwareService $userContextAwareService
    ) {
    }

    /**
     * Get a section by its ID with its fields and translations
     * @param int|null $page_id
     * @param int $section_id
     * @return array
     * @throws ServiceException If section not found or access denied
     */
    public function getSection(?int $page_id, int $section_id): array
    {
        $cacheKey = "section_{$section_id}_" . ($page_id ?? 'auto');

        return $this->cache
            ->withCategory(CacheService::CATEGORY_SECTIONS)
            ->withEntityScope(CacheService::ENTITY_SCOPE_SECTION, $section_id)
            ->getItem(
                $cacheKey,
                fn() => $this->fetchSectionFromDatabase($page_id, $section_id)
            );
    }

    private function fetchSectionFromDatabase(?int $page_id, int $section_id): array
    {
        // Fetch section
        $section = $this->sectionRepository->find($section_id);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }

        // If page_id is not provided, find it from the section
        if ($page_id === null) {
            // Get the page from the section by finding which page this section belongs to
            $pageSection = $this->entityManager->getRepository(PagesSection::class)
                ->findOneBy(['section' => $section_id]);

            if ($pageSection) {
                $page = $pageSection->getPage();
                if ($page) {
                    $page_id = $page->getId();
                }
            }

            if (!$page_id) {
                $this->throwNotFound('Page not found for this section');
            }
        }

        // Get page entity for permission check
        $page = $this->pageRepository->find($page_id);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Permission check
        $this->userContextAwareService->checkAccess($page->getKeyword(), 'select');
        $this->sectionRelationshipService->checkSectionInPage($page_id, $section_id);

        // Get fields using the dedicated service
        $formattedFields = $this->sectionFieldService->getSectionFields($section);

        // Get languages from the formatted fields
        $languages = [];
        foreach ($formattedFields as $field) {
            foreach ($field['translations'] as $translation) {
                if (isset($translation['language_id']) && $translation['language_id'] > 1) {
                    $langId = $translation['language_id'];
                    $languages[$langId] = [
                        'id' => $langId,
                        'locale' => $translation['language_code'] ?? null,
                    ];
                }
            }
        }
        $languages = array_values($languages);

        return [
            'section' => $this->normalizeSection($section),
            'fields' => $formattedFields,
            'languages' => $languages,
        ];
    }

    /**
     * Get all children sections for a parent section
     * @param int $page_id
     * @param int $parent_section_id
     * @return array
     */
    public function getChildrenSections(int $page_id, int $parent_section_id): array
    {
        // Get page entity for permission check
        $page = $this->pageRepository->find($page_id);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        $this->userContextAwareService->checkAccess($page->getKeyword(), 'select');
        $this->sectionRelationshipService->checkSectionInPage($page_id, $parent_section_id);
        $hierarchies = $this->entityManager->getRepository(SectionsHierarchy::class)
            ->findBy(['parent' => $parent_section_id], ['position' => 'ASC']);
        $sections = [];
        foreach ($hierarchies as $hierarchy) {
            $child = $hierarchy->getChildSection();
            if ($child) {
                $sections[] = $this->normalizeSection($child);
            }
        }
        return $sections;
    }

    /**
     * Normalize a Section entity for API response
     * @param Section $section
     * @return array
     */
    protected function normalizeSection($section): array
    {
        // Use the common utility service for basic normalization
        $normalizedSection = $this->sectionUtilityService->normalizeSection($section);

        // Add admin-specific fields
        $style = $section->getStyle();
        $styleData = null;

        if ($style) {
            $styleData = [
                'id' => $style->getId(),
                'name' => $style->getName(),
                'description' => $style->getDescription(),
                'typeId' => $style->getIdType(),
                'type' => $style->getType() ? $style->getType()->getLookupValue() : null,
                'canHaveChildren' => $style->getCanHaveChildren(),
            ];
        }

        // Merge with utility service normalization and add admin-specific fields
        return array_merge($normalizedSection, [
            'style' => $styleData
        ]);
    }

    /**
     * Adds a child section to a parent section.
     * 
     * @param int $page_id The page ID.
     * @param int $parent_section_id The ID of the parent section.
     * @param int $child_section_id The ID of the child section.
     * @param int|null $position The desired position.
     * @param int|null $oldParentPageId The ID of the old parent page to remove the relationship from (optional).
     * @param int|null $oldParentSectionId The ID of the old parent section to remove the relationship from (optional).
     * @return SectionsHierarchy The new section hierarchy relationship.
     * @throws ServiceException If the relationship already exists or entities are not found.
     */
    public function addSectionToSection(int $page_id, int $parent_section_id, int $child_section_id, ?int $position, ?int $oldParentPageId = null, ?int $oldParentSectionId = null): SectionsHierarchy
    {
        $result = $this->sectionRelationshipService->addSectionToSection($page_id, $parent_section_id, $child_section_id, $position, $oldParentPageId, $oldParentSectionId);
        
        // Invalidate section-specific cache
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $parent_section_id);
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $child_section_id);
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page_id);
        
        // Invalidate all section lists in category
        $this->cache->withCategory(CacheService::CATEGORY_SECTIONS)->invalidateAllListsInCategory();
        
        return $result;
    }

    /**
     * Removes a child section from a parent section and returns the removed Section entity.
     * 
     * @param int $page_id The page ID.
     * @param int $parent_section_id The ID of the parent section.
     * @param int $child_section_id The ID of the child section.
     * @throws ServiceException If the relationship does not exist.
     */
    public function removeSectionFromSection(int $page_id, int $parent_section_id, int $child_section_id): void
    {
        $this->sectionRelationshipService->removeSectionFromSection($page_id, $parent_section_id, $child_section_id);
        
        // Invalidate section-specific cache
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $parent_section_id);
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $child_section_id);
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page_id);
        
        // Invalidate all section lists in category
        $this->cache->withCategory(CacheService::CATEGORY_SECTIONS)->invalidateAllListsInCategory();
    }

    /**
     * Deletes a section permanently.
     *
     * This will remove the section and all its relationships (parent, child, and page attachments).
     *
     * @param int|null $page_id The page ID.
     * @param int $section_id The ID of the section to delete.
     * @throws ServiceException If the section is not found.
     */
    public function deleteSection(?int $page_id, int $section_id): void
    {
        $this->sectionRelationshipService->deleteSection($page_id, $section_id);
        
        // Invalidate section-specific cache
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $section_id);
        if ($page_id) {
            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page_id);
        }
        
        // Invalidate all section lists in category
        $this->cache->withCategory(CacheService::CATEGORY_SECTIONS)->invalidateAllListsInCategory();
    }

    /**
     * Force deletes a section permanently (always delete, never just remove from page).
     *
     * This will always completely delete the section and all its relationships,
     * unlike deleteSection which might just remove from page for direct associations.
     *
     * @param int $page_id The page ID.
     * @param int $section_id The ID of the section to force delete.
     * @throws ServiceException If the section is not found or access denied.
     */
    public function forceDeleteSection(int $page_id, int $section_id): void
    {
        $this->sectionRelationshipService->forceDeleteSection($page_id, $section_id);
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $section_id);
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page_id);
    }

    /**
     * Creates a new section with the specified style and adds it to a page
     *
     * @param int $page_id The ID of the page to add the section to
     * @param int $styleId The ID of the style to use for the section
     * @param int|null $position The position of the section on the page
     * @return array The ID and position of the new section
     * @throws ServiceException If the page or style is not found
     */
    public function createPageSection(int $page_id, int $styleId, ?int $position): array
    {
        $result = $this->sectionCreationService->createPageSection($page_id, $styleId, $position);
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page_id);
        return $result;
    }

    /**
     * Creates a new section with the specified style and adds it as a child to another section
     *
     * @param int|null $page_id The page ID.
     * @param int $parent_section_id The ID of the parent section
     * @param int $styleId The ID of the style to use for the section
     * @param int|null $position The position of the child section
     * @return array The ID and position of the new section
     * @throws ServiceException If the parent section or style is not found
     */
    public function createChildSection(?int $page_id, int $parent_section_id, int $styleId, ?int $position): array
    {
        $result = $this->sectionCreationService->createChildSection($page_id, $parent_section_id, $styleId, $position);
        if ($page_id) {
            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page_id);
        }
        $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $parent_section_id);
        return $result;
    }

    /**
     * Update an existing section and its field translations
     * 
     * @param int $pageId The ID of the page the section belongs to
     * @param int $sectionId The ID of the section to update
     * @param string $sectionName The new name for the section
     * @param array $contentFields The content fields to update (display=1 fields)
     * @param array $propertyFields The property fields to update (display=0 fields)
     * @return Section The updated section
     * @throws ServiceException If section not found or access denied
     */
    public function updateSection(int $pageId, int $sectionId, ?string $sectionName, array $contentFields, array $propertyFields): Section
    {
        $this->entityManager->beginTransaction();

        try {
            // Find the section
            $section = $this->sectionRepository->find($sectionId);
            if (!$section) {
                $this->throwNotFound('Section not found');
            }

            // Get page entity for permission check
            $page = $this->pageRepository->find($pageId);
            if (!$page) {
                $this->throwNotFound('Page not found');
            }

            // Check if user has update access to the page
            $this->userContextAwareService->checkAccess($page->getKeyword(), 'update');
            $this->sectionRelationshipService->checkSectionInPage($pageId, $sectionId);

            // Store original section for transaction logging
            $originalSection = clone $section;

            // Update section name
            if ($sectionName) {
                $section->setName($sectionName);
            }

            // Flush section changes first to ensure we have a valid section ID
            $this->entityManager->flush();

            // Update field translations using dedicated service
            $this->sectionFieldService->updateSectionFields($section, $contentFields, $propertyFields);

            // Flush all changes again
            $this->entityManager->flush();

            // Log the transaction
            $this->transactionService->logTransaction(
                LookupService::TRANSACTION_TYPES_UPDATE,
                LookupService::TRANSACTION_BY_BY_USER,
                'sections',
                $section->getId(),
                (object) array("old_section" => $originalSection, "new_section" => $section),
                'Section updated: ' . $section->getName() . ' (ID: ' . $section->getId() . ')'
            );

            $this->entityManager->commit();
            
            // Invalidate cache for this specific section
            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $sectionId);
            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $pageId);
            
            return $section;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to update section: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Export all sections of a given page (including all nested sections) as JSON
     * 
     * @param int $page_id The ID of the page to export sections from
     * @return array JSON-serializable array with all page sections
     * @throws ServiceException If page not found or access denied
     */
    public function exportPageSections(int $page_id): array
    {
        // Get the page
        $page = $this->pageRepository->find($page_id);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Permission check
        $this->userContextAwareService->checkAccess($page->getKeyword(), 'select');

        // Use existing hierarchical fetching method
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());

        if (empty($flatSections)) {
            return [];
        }

        // Build hierarchical structure using existing utility method
        $hierarchicalSections = $this->sectionUtilityService->buildNestedSections($flatSections, false);

        // Add field translations to the hierarchical structure
        $this->addFieldTranslationsToSections($hierarchicalSections);

        return $hierarchicalSections;
    }

    /**
     * Export a selected section (and all of its nested children) as JSON
     * 
     * @param int $page_id The ID of the page containing the section
     * @param int $section_id The ID of the section to export
     * @return array JSON-serializable array with the section and its children
     * @throws ServiceException If section not found or access denied
     */
    public function exportSection(int $page_id, int $section_id): array
    {
        // Get the page
        $page = $this->pageRepository->find($page_id);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Permission check
        $this->userContextAwareService->checkAccess($page->getKeyword(), 'select');
        $this->sectionRelationshipService->checkSectionInPage($page_id, $section_id);

        // Get the section
        $section = $this->sectionRepository->find($section_id);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }

        // Get all sections for the page using existing method
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());

        // Build hierarchical structure
        $hierarchicalSections = $this->sectionUtilityService->buildNestedSections($flatSections, false);

        // Find the specific section and its subtree
        $targetSection = $this->findSectionInHierarchy($hierarchicalSections, $section_id);

        if (!$targetSection) {
            // If not found in hierarchy, create a basic structure from the section entity
            $style = $section->getStyle();
            $targetSection = [
                'id' => $section->getId(),
                'name' => $section->getName(),
                'style_name' => $style ? $style->getName() : null,
                'children' => $this->getChildrenSectionsForExport($page->getId(), $section_id)
            ];
        }

        // Add field translations to the section subtree
        $sectionsArray = [$targetSection];
        $this->addFieldTranslationsToSections($sectionsArray);

        return $sectionsArray;
    }

    /**
     * Get children sections for export in hierarchical format
     * 
     * @param int $pageId The page ID
     * @param int $parentSectionId The parent section ID
     * @return array Array of child sections
     */
    private function getChildrenSectionsForExport(int $pageId, int $parentSectionId): array
    {
        // Get child sections using sections hierarchy
        $childHierarchies = $this->entityManager->getRepository(SectionsHierarchy::class)
            ->findBy(['parent' => $parentSectionId], ['position' => 'ASC']);

        $children = [];
        foreach ($childHierarchies as $hierarchy) {
            $childSection = $hierarchy->getChildSection();
            if ($childSection) {
                $style = $childSection->getStyle();
                $childData = [
                    'id' => $childSection->getId(),
                    'name' => $childSection->getName(),
                    'style_name' => $style ? $style->getName() : null,
                    'children' => $this->getChildrenSectionsForExport($pageId, $childSection->getId())
                ];
                $children[] = $childData;
            }
        }

        return $children;
    }

    /**
     * Find a section in hierarchical structure recursively
     * 
     * @param array $sections Hierarchical sections array
     * @param int $sectionId The section ID to find
     * @return array|null The found section with its children, or null if not found
     */
    private function findSectionInHierarchy(array $sections, int $sectionId): ?array
    {
        foreach ($sections as $section) {
            // Use strict comparison to ensure type safety
            if (isset($section['id']) && (int) $section['id'] === $sectionId) {
                return $section;
            }

            // Search in children recursively
            if (!empty($section['children'])) {
                $found = $this->findSectionInHierarchy($section['children'], $sectionId);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Add field translations to sections recursively (modular method)
     * Only exports field names with their values - minimal data needed for import
     * 
     * @param array &$sections Hierarchical sections array (passed by reference)
     */
    private function addFieldTranslationsToSections(array &$sections): void
    {
        foreach ($sections as &$section) {
            $sectionId = $section['id'] ?? null;
            if (!$sectionId) {
                continue;
            }

            // Clean up section structure - keep only essential fields
            $cleanSection = [
                'name' => $section['name'] ?? '',
                'style_name' => $section['style_name'] ?? null,
                'children' => [],
                'fields' => (object) []
            ];

            // Get all translations for this section
            $translations = $this->entityManager->getRepository(SectionsFieldsTranslation::class)
                ->createQueryBuilder('t')
                ->leftJoin('t.field', 'f')
                ->leftJoin('t.language', 'l')
                ->where('t.section = :sectionId')
                ->setParameter('sectionId', $sectionId)
                ->getQuery()
                ->getResult();

            $fields = [];
            foreach ($translations as $translation) {
                $field = $translation->getField();
                $language = $translation->getLanguage();

                if (!$field || !$language) {
                    continue;
                }

                $fieldName = $field->getName();
                $locale = $language->getLocale();

                // Initialize field if not exists
                if (!isset($fields[$fieldName])) {
                    $fields[$fieldName] = [];
                }

                // Store translation by locale only
                $fields[$fieldName][$locale] = [
                    'content' => $translation->getContent(),
                    'meta' => $translation->getMeta()
                ];
            }

            // Add fields to clean section - use object if empty to match JSON schema
            $cleanSection['fields'] = empty($fields) ? (object) [] : $fields;

            // Process children recursively
            if (!empty($section['children'])) {
                $this->addFieldTranslationsToSections($section['children']);
                $cleanSection['children'] = $section['children'];
            }

            // Replace the section with clean version
            $section = $cleanSection;
        }
    }

    /**
     * Import sections from JSON into a target page
     * 
     * @param int $page_id The ID of the target page
     * @param array $sectionsData The sections data to import
     * @param int|null $position The position where the sections should be inserted
     * @return array Result of the import operation
     * @throws ServiceException If page not found or access denied
     */
    public function importSectionsToPage(int $page_id, array $sectionsData, ?int $position = null): array
    {
        // Get the page
        $page = $this->pageRepository->find($page_id);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Permission check
        $this->userContextAwareService->checkAccess($page->getKeyword(), 'update');

        // Start transaction
        $this->entityManager->beginTransaction();

        try {
            $importedSections = $this->importSections($sectionsData, $page, null, $position);

            // Normalize positions after import
            $this->positionManagementService->normalizePageSectionPositions($page->getId(), true);

            // Commit transaction
            $this->entityManager->commit();

            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page->getId());

            return $importedSections;
        } catch (\Throwable $e) {
            // Rollback transaction
            $this->entityManager->rollback();

            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to import sections: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Import sections from JSON into a specific section
     * 
     * @param int $page_id The ID of the target page
     * @param int $parent_section_id The ID of the parent section to import into
     * @param array $sectionsData The sections data to import
     * @param int|null $position The position where the sections should be inserted
     * @return array Result of the import operation
     * @throws ServiceException If section not found or access denied
     */
    public function importSectionsToSection(int $page_id, int $parent_section_id, array $sectionsData, ?int $position = null): array
    {
        // Get the page
        $page = $this->pageRepository->find($page_id);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Permission check
        $this->userContextAwareService->checkAccess($page->getKeyword(), 'update');
        $this->sectionRelationshipService->checkSectionInPage($page_id, $parent_section_id);

        // Get the parent section
        $parentSection = $this->sectionRepository->find($parent_section_id);
        if (!$parentSection) {
            $this->throwNotFound('Parent section not found');
        }

        // Start transaction
        $this->entityManager->beginTransaction();

        try {
            $importedSections = $this->importSections($sectionsData, null, $parentSection, $position);

            // Normalize positions after import
            $this->positionManagementService->normalizeSectionHierarchyPositions($parent_section_id, true);

            // Commit transaction
            $this->entityManager->commit();

            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page_id);
            $this->cache->invalidateEntityScope(CacheService::ENTITY_SCOPE_SECTION, $parent_section_id);

            return $importedSections;
        } catch (\Throwable $e) {
            // Rollback transaction
            $this->entityManager->rollback();

            throw $e instanceof ServiceException ? $e : new ServiceException(
                'Failed to import sections: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['previous_exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Import sections from JSON data
     * 
     * @param array $sectionsData The sections data to import
     * @param Page|null $page The target page (if importing to page)
     * @param Section|null $parentSection The parent section (if importing to section)
     * @param int|null $globalPosition The global position for the first level of imported sections
     * @return array Result of the import operation
     */
    private function importSections(array $sectionsData, ?Page $page = null, ?Section $parentSection = null, ?int $globalPosition = null): array
    {
        $importedSections = [];
        $currentPosition = $globalPosition;

        foreach ($sectionsData as $index => $sectionData) {
            // Create new section
            $section = new Section();

            // Add timestamp suffix to section name to ensure uniqueness
            $timestamp = time();
            $baseName = $sectionData['name'] ?? 'Imported Section';
            $section->setName($baseName . '-' . $timestamp);

            // Find style by name
            $styleName = $sectionData['style_name'] ?? null;
            if ($styleName) {
                $style = $this->styleRepository->findOneBy(['name' => $styleName]);
                if ($style) {
                    $section->setStyle($style);
                } else {
                    // Log warning but continue with import
                    $this->transactionService->logTransaction(
                        LookupService::TRANSACTION_TYPES_UPDATE, // Using update type for warnings
                        LookupService::TRANSACTION_BY_BY_USER,
                        'sections',
                        0,
                        (object) ['message' => "Style not found: {$styleName}", 'warning' => true],
                        "Style not found during section import: {$styleName}"
                    );
                }
            }

            // Persist section
            $this->entityManager->persist($section);
            $this->entityManager->flush();

            // Import fields and translations using new simplified format
            if (isset($sectionData['fields']) && is_array($sectionData['fields']) && !empty($sectionData['fields'])) {
                $this->importSectionFieldsSimplified($section, $sectionData['fields']);
            }

            // Determine position for this section
            $sectionPosition = null;
            if ($currentPosition !== null) {
                // Use the global position for the first section, then increment
                $sectionPosition = $currentPosition + $index;
            } else {
                // Use section-specific position if provided, otherwise auto-assign
                $sectionPosition = $sectionData['position'] ?? null;
            }

            // Add section to page or parent section
            if ($page) {
                // Add to page
                $pageSection = new PagesSection();
                $pageSection->setPage($page);
                $pageSection->setSection($section);

                if ($sectionPosition !== null) {
                    $pageSection->setPosition($sectionPosition);
                } else {
                    // Auto-assign position if not provided
                    $maxPosition = $this->entityManager->createQueryBuilder()
                        ->select('MAX(ps.position)')
                        ->from(PagesSection::class, 'ps')
                        ->where('ps.page = :page')
                        ->setParameter('page', $page)
                        ->getQuery()
                        ->getSingleScalarResult();
                    $pageSection->setPosition(($maxPosition ?? 0) + 1);
                }

                $this->entityManager->persist($pageSection);
            } elseif ($parentSection) {
                // Add to parent section
                $sectionHierarchy = new SectionsHierarchy();
                $sectionHierarchy->setParentSection($parentSection);
                $sectionHierarchy->setChildSection($section);

                if ($sectionPosition !== null) {
                    $sectionHierarchy->setPosition($sectionPosition);
                } else {
                    // Auto-assign position if not provided
                    $maxPosition = $this->entityManager->createQueryBuilder()
                        ->select('MAX(sh.position)')
                        ->from(SectionsHierarchy::class, 'sh')
                        ->where('sh.parentSection = :parent')
                        ->setParameter('parent', $parentSection)
                        ->getQuery()
                        ->getSingleScalarResult();
                    $sectionHierarchy->setPosition(($maxPosition ?? 0) + 1);
                }

                $this->entityManager->persist($sectionHierarchy);
            }

            $this->entityManager->flush();

            // Record the imported section
            $importedSections[] = [
                'id' => $section->getId(),
                'name' => $section->getName(),
                'style_name' => $styleName,
                'position' => $sectionPosition
            ];

            // Import child sections recursively if present
            if (isset($sectionData['children']) && is_array($sectionData['children'])) {
                $childResults = $this->importSections($sectionData['children'], null, $section);
                $importedSections = array_merge($importedSections, $childResults);
            }
        }

        return $importedSections;
    }


    /**
     * Import section fields using simplified format (modular method)
     * Only processes field names with their values
     * 
     * @param Section $section The section to import fields for
     * @param array $fieldsData The simplified fields data to import
     */
    private function importSectionFieldsSimplified(Section $section, array $fieldsData): void
    {
        foreach ($fieldsData as $fieldName => $localeData) {
            // Find field by name
            $field = $this->entityManager->getRepository(Field::class)
                ->findOneBy(['name' => $fieldName]);

            if (!$field) {
                // Skip fields that don't exist in the system
                continue;
            }

            // Process each locale
            foreach ($localeData as $locale => $translationData) {
                // Find language by locale
                $language = $this->entityManager->getRepository(Language::class)
                    ->findOneBy(['locale' => $locale]);

                if (!$language) {
                    // Skip translations for languages that don't exist
                    continue;
                }

                $content = $translationData['content'] ?? '';
                $meta = $translationData['meta'] ?? null;

                // Check if translation already exists
                $existingTranslation = $this->entityManager->getRepository(SectionsFieldsTranslation::class)
                    ->findOneBy([
                        'section' => $section,
                        'field' => $field,
                        'language' => $language,
                    ]);

                if ($existingTranslation) {
                    // Update existing translation
                    $existingTranslation->setContent($content);
                    if ($meta !== null) {
                        $existingTranslation->setMeta($meta);
                    }
                } else {
                    // Create new translation
                    $translation = new SectionsFieldsTranslation();
                    $translation->setSection($section);
                    $translation->setField($field);
                    $translation->setLanguage($language);
                    $translation->setContent($content);
                    if ($meta !== null) {
                        $translation->setMeta($meta);
                    }

                    $this->entityManager->persist($translation);
                }
            }
        }

        $this->entityManager->flush();
    }

}
