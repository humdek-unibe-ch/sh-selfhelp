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
        private readonly SectionExportImportService $sectionExportImportService,
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

        // Add global fields structure
        $globalFields = [
            'condition' => $section->getCondition(),
            'data_config' => $section->getDataConfig(),
            'css' => $section->getCss(),
            'css_mobile' => $section->getCssMobile(),
            'debug' => $section->isDebug(),
        ];

        // Merge with utility service normalization and add admin-specific fields
        return array_merge($normalizedSection, [
            'style' => $styleData,
            'global_fields' => $globalFields
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
     * @param array $globalFields The global fields to update (condition, data_config, css, css_mobile, debug)
     * @return Section The updated section
     * @throws ServiceException If section not found or access denied
     */
    public function updateSection(int $pageId, int $sectionId, ?string $sectionName, array $contentFields, array $propertyFields, ?array $globalFields = null): Section
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

            // Update global fields if provided (null means not provided, empty array means clear all, populated array means update specific fields)
            if ($globalFields !== null) {
                // If globalFields is an empty array (sent as {}), clear all global fields
                if (empty($globalFields)) {
                    $section->setCondition(null);
                    $section->setDataConfig(null);
                    $section->setCss(null);
                    $section->setCssMobile(null);
                    $section->setDebug(false);
                } else {
                    // Update only provided fields, leave others unchanged
                    if (array_key_exists('condition', $globalFields)) {
                        $section->setCondition($globalFields['condition']);
                    }
                    if (array_key_exists('data_config', $globalFields)) {
                        $section->setDataConfig($globalFields['data_config']);
                    }
                    if (array_key_exists('css', $globalFields)) {
                        $section->setCss($globalFields['css']);
                    }
                    if (array_key_exists('css_mobile', $globalFields)) {
                        $section->setCssMobile($globalFields['css_mobile']);
                    }
                    if (array_key_exists('debug', $globalFields)) {
                        $section->setDebug($globalFields['debug'] === null ? false : (bool)$globalFields['debug']);
                    }
                }
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
        return $this->sectionExportImportService->exportPageSections($page_id);
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
        return $this->sectionExportImportService->exportSection($page_id, $section_id);
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
            // Delegate core import logic to SectionExportImportService
            $importedSections = $this->sectionExportImportService->importSectionsToPage($page_id, $sectionsData, $position);

            // Additional AdminSectionService-specific functionality
            // Normalize positions after import
            $this->positionManagementService->normalizePageSectionPositions($page->getId(), true);

            // Commit transaction
            $this->entityManager->commit();

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
            // Delegate core import logic to SectionExportImportService
            $importedSections = $this->sectionExportImportService->importSectionsToSection($page_id, $parent_section_id, $sectionsData, $position);

            // Additional AdminSectionService-specific functionality
            // Normalize positions after import
            $this->positionManagementService->normalizeSectionHierarchyPositions($parent_section_id, true);

            // Commit transaction
            $this->entityManager->commit();

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






}
