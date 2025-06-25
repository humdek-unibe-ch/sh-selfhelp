<?php

namespace App\Service\CMS\Admin;

use App\Entity\Field;
use App\Entity\FieldType;
use App\Entity\Page;
use App\Entity\PagesSection;
use App\Entity\Section;
use App\Entity\SectionsFieldsTranslation;
use App\Entity\SectionsHierarchy;
use App\Entity\Style;
use App\Entity\StylesField;
use App\Exception\ServiceException;
use App\Repository\SectionRepository;
use App\Repository\StyleRepository;
use App\Repository\StylesFieldRepository;
use App\Repository\PageRepository;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Core\TransactionService;
use App\Service\Core\UserContextAwareService;
use App\Service\CMS\Common\SectionUtilityService;
use App\Service\CMS\Admin\SectionFieldService;
use App\Service\CMS\Admin\SectionRelationshipService;
use App\Service\CMS\Admin\SectionCreationService;
use App\Service\CMS\Admin\SectionExportImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling section-related operations in the admin panel
 * MEMORY_RULE - I am Claude Sonnet 4, your AI coding assistant in Cursor
 */
class AdminSectionService extends UserContextAwareService
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransactionService $transactionService,
        private readonly StyleRepository $styleRepository,
        private readonly StylesFieldRepository $stylesFieldRepository,
        private readonly PositionManagementService $positionManagementService,
        private readonly SectionUtilityService $sectionUtilityService,
        private readonly SectionFieldService $sectionFieldService,
        private readonly SectionRelationshipService $sectionRelationshipService,
        private readonly SectionCreationService $sectionCreationService,
        private readonly SectionExportImportService $sectionExportImportService,
        ACLService $aclService,
        UserContextService $userContextService,
        PageRepository $pageRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct($userContextService, $aclService, $pageRepository, $sectionRepository);
        $this->sectionUtilityService->setStylesFieldRepository($this->stylesFieldRepository);
    }

    /**
     * Get a section by its ID with its fields and translations
     * @param string|null $page_keyword
     * @param int $section_id
     * @return array
     * @throws ServiceException If section not found or access denied
     */
    public function getSection(?string $page_keyword, int $section_id): array
    {
        // Fetch section
        $section = $this->sectionRepository->find($section_id);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }
        
        // If page_keyword is not provided, find it from the section
        if ($page_keyword === null) {
            // Get the page from the section by finding which page this section belongs to
            $pageSection = $this->entityManager->getRepository(PagesSection::class)
                ->findOneBy(['section' => $section_id]);
            
            if ($pageSection) {
                $page = $pageSection->getPage();
                if ($page) {
                    $page_keyword = $page->getKeyword();
                }
            }
            
            if (!$page_keyword) {
                $this->throwNotFound('Page not found for this section');
            }
        }
        
        // Permission check
        $this->checkAccess($page_keyword, 'select');
        $this->checkSectionInPage($page_keyword, $section_id);

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
     * @param int $parent_section_id
     * @return array
     */
    public function getChildrenSections(string $page_keyword, int $parent_section_id): array
    {
        $this->checkAccess($page_keyword, 'select');
        $this->checkSectionInPage($page_keyword,$parent_section_id);
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
                'canHaveChildren' => $style->getCanHaveChildren()
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
     * @param string $page_keyword The page keyword.
     * @param int $parent_section_id The ID of the parent section.
     * @param int $child_section_id The ID of the child section.
     * @param int|null $position The desired position.
     * @param string|null $oldParentPageKeyword The keyword of the old parent page to remove the relationship from (optional).
     * @param int|null $oldParentSectionId The ID of the old parent section to remove the relationship from (optional).
     * @return SectionsHierarchy The new section hierarchy relationship.
     * @throws ServiceException If the relationship already exists or entities are not found.
     */
    public function addSectionToSection(string $page_keyword, int $parent_section_id, int $child_section_id, ?int $position, ?string $oldParentPageKeyword = null, ?int $oldParentSectionId = null): SectionsHierarchy
    {
        return $this->sectionRelationshipService->addSectionToSection($page_keyword, $parent_section_id, $child_section_id, $position, $oldParentPageKeyword, $oldParentSectionId);
    }

    /**
     * Removes a child section from a parent section and returns the removed Section entity.
     * 
     * @param string $page_keyword The page keyword.
     * @param int $parent_section_id The ID of the parent section.
     * @param int $child_section_id The ID of the child section.
     * @throws ServiceException If the relationship does not exist.
     */
    public function removeSectionFromSection(string $page_keyword, int $parent_section_id, int $child_section_id): void
    {
        $this->sectionRelationshipService->removeSectionFromSection($page_keyword, $parent_section_id, $child_section_id);
    }

    /**
     * Deletes a section permanently.
     *
     * This will remove the section and all its relationships (parent, child, and page attachments).
     *
     * @param string|null $page_keyword The page keyword.
     * @param int $section_id The ID of the section to delete.
     * @throws ServiceException If the section is not found.
     */
    public function deleteSection(?string $page_keyword, int $section_id): void
    {
        $this->sectionRelationshipService->deleteSection($page_keyword, $section_id);
    }

    /**
     * Creates a new section with the specified style and adds it to a page
     *
     * @param string $page_keyword The keyword of the page to add the section to
     * @param int $styleId The ID of the style to use for the section
     * @param int|null $position The position of the section on the page
     * @return array The ID and position of the new section
     * @throws ServiceException If the page or style is not found
     */
    public function createPageSection(string $page_keyword, int $styleId, ?int $position): array
    {
        return $this->sectionCreationService->createPageSection($page_keyword, $styleId, $position);
    }

    /**
     * Creates a new section with the specified style and adds it as a child to another section
     *
     * @param string|null $page_keyword The page keyword.
     * @param int $parent_section_id The ID of the parent section
     * @param int $styleId The ID of the style to use for the section
     * @param int|null $position The position of the child section
     * @return array The ID and position of the new section
     * @throws ServiceException If the parent section or style is not found
     */
    public function createChildSection(?string $page_keyword, int $parent_section_id, int $styleId, ?int $position): array
    {
        return $this->sectionCreationService->createChildSection($page_keyword, $parent_section_id, $styleId, $position);
    }

    /**
     * Update an existing section and its field translations
     * 
     * @param string $pageKeyword The keyword of the page the section belongs to
     * @param int $sectionId The ID of the section to update
     * @param string $sectionName The new name for the section
     * @param array $contentFields The content fields to update (display=1 fields)
     * @param array $propertyFields The property fields to update (display=0 fields)
     * @return Section The updated section
     * @throws ServiceException If section not found or access denied
     */
    public function updateSection(string $pageKeyword, int $sectionId, ?string $sectionName, array $contentFields, array $propertyFields): Section
    {
        $this->entityManager->beginTransaction();

        try {
            // Find the section
            $section = $this->sectionRepository->find($sectionId);
            if (!$section) {
                $this->throwNotFound('Section not found');
            }

            // Check if user has update access to the page
            $this->checkAccess($pageKeyword, 'update');
            $this->checkSectionInPage($pageKeyword, $sectionId);

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
                \App\Service\Core\LookupService::TRANSACTION_TYPES_UPDATE,
                \App\Service\Core\LookupService::TRANSACTION_BY_BY_USER,
                'sections',
                $section->getId(),
                (object) array("old_section" => $originalSection, "new_section" => $section),
                'Section updated: ' . $section->getName() . ' (ID: ' . $section->getId() . ')'
            );

            $this->entityManager->commit();
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
     * @param string $page_keyword The keyword of the page to export sections from
     * @return array JSON-serializable array with all page sections
     * @throws ServiceException If page not found or access denied
     */
    public function exportPageSections(string $page_keyword): array
    {
        // Permission check
        $this->checkAccess($page_keyword, 'select');
        
        // Get the page
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Use existing hierarchical fetching method
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        
        if (empty($flatSections)) {
            return [];
        }
        
        // Build hierarchical structure using existing utility method
        $hierarchicalSections = $this->sectionUtilityService->buildNestedSections($flatSections);
        
        // Add field translations to the hierarchical structure
        $this->addFieldTranslationsToSections($hierarchicalSections);
        
        return $hierarchicalSections;
    }
    
    /**
     * Export a selected section (and all of its nested children) as JSON
     * 
     * @param string $page_keyword The keyword of the page containing the section
     * @param int $section_id The ID of the section to export
     * @return array JSON-serializable array with the section and its children
     * @throws ServiceException If section not found or access denied
     */
    public function exportSection(string $page_keyword, int $section_id): array
    {
        // Permission check
        $this->checkAccess($page_keyword, 'select');
        $this->checkSectionInPage($page_keyword, $section_id);
        
        // Get the section
        $section = $this->sectionRepository->find($section_id);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }
        
        // Get the page to use existing hierarchical method
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Get all sections for the page using existing method
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        
        // Build hierarchical structure
        $hierarchicalSections = $this->sectionUtilityService->buildNestedSections($flatSections);
        
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
            if (isset($section['id']) && (int)$section['id'] === $sectionId) {
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
                'fields' => []
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
            
            // Build minimal fields data - only field names with values (no gender)
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
                
                // Store translation by locale only (no gender)
                $fields[$fieldName][$locale] = [
                    'content' => $translation->getContent(),
                    'meta' => $translation->getMeta()
                ];
            }
            
            // Add fields to clean section
            $cleanSection['fields'] = $fields;
            
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
     * @param string $page_keyword The keyword of the target page
     * @param array $sectionsData The sections data to import
     * @return array Result of the import operation
     * @throws ServiceException If page not found or access denied
     */
    public function importSectionsToPage(string $page_keyword, array $sectionsData): array
    {
        // Permission check
        $this->checkAccess($page_keyword, 'update');
        
        // Get the page
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Start transaction
        $this->entityManager->beginTransaction();
        
        try {
            $importedSections = $this->importSections($sectionsData, $page);
            
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
     * @param string $page_keyword The keyword of the target page
     * @param int $parent_section_id The ID of the parent section to import into
     * @param array $sectionsData The sections data to import
     * @return array Result of the import operation
     * @throws ServiceException If section not found or access denied
     */
    public function importSectionsToSection(string $page_keyword, int $parent_section_id, array $sectionsData): array
    {
        // Permission check
        $this->checkAccess($page_keyword, 'update');
        $this->checkSectionInPage($page_keyword, $parent_section_id);
        
        // Get the parent section
        $parentSection = $this->sectionRepository->find($parent_section_id);
        if (!$parentSection) {
            $this->throwNotFound('Parent section not found');
        }
        
        // Start transaction
        $this->entityManager->beginTransaction();
        
        try {
            $importedSections = $this->importSections($sectionsData, null, $parentSection);
            
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
     * Import sections from JSON data
     * 
     * @param array $sectionsData The sections data to import
     * @param Page|null $page The target page (if importing to page)
     * @param Section|null $parentSection The parent section (if importing to section)
     * @return array Result of the import operation
     */
    private function importSections(array $sectionsData, ?Page $page = null, ?Section $parentSection = null): array
    {
        $importedSections = [];
        
        foreach ($sectionsData as $sectionData) {
            // Create new section
            $section = new Section();
            $section->setName($sectionData['name'] ?? 'Imported Section');
            
            // Find style by name
            $styleName = $sectionData['style_name'] ?? null;
            if ($styleName) {
                $style = $this->styleRepository->findOneBy(['name' => $styleName]);
                if ($style) {
                    $section->setStyle($style);
                } else {
                    // Log warning but continue with import
                    $this->transactionService->logTransaction(
                        \App\Service\Core\LookupService::TRANSACTION_TYPES_UPDATE, // Using update type for warnings
                        \App\Service\Core\LookupService::TRANSACTION_BY_BY_USER,
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
            if (isset($sectionData['fields']) && is_array($sectionData['fields'])) {
                $this->importSectionFieldsSimplified($section, $sectionData['fields']);
            }
            
            // Get position from data or use auto-increment
            $position = $sectionData['position'] ?? null;
            
            // Add section to page or parent section
            if ($page) {
                // Add to page
                $pageSection = new PagesSection();
                $pageSection->setPage($page);
                $pageSection->setIdPages($page->getId());
                $pageSection->setSection($section);
                $pageSection->setIdSections($section->getId());
                
                if ($position !== null) {
                    $pageSection->setPosition($position);
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
                
                if ($position !== null) {
                    $sectionHierarchy->setPosition($position);
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
                'position' => $position
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
     * Only processes field names with their values - minimal data needed (no gender)
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
                $language = $this->entityManager->getRepository(\App\Entity\Language::class)
                    ->findOneBy(['locale' => $locale]);
                
                if (!$language) {
                    // Skip translations for languages that don't exist
                    continue;
                }
                
                // Use default gender (ID 1) since gender is being removed
                $gender = $this->entityManager->getRepository(\App\Entity\Gender::class)
                    ->find(1);
                
                $content = $translationData['content'] ?? '';
                $meta = $translationData['meta'] ?? null;
                
                // Check if translation already exists
                $existingTranslation = $this->entityManager->getRepository(SectionsFieldsTranslation::class)
                    ->findOneBy([
                        'section' => $section,
                        'field' => $field,
                        'language' => $language,
                        'gender' => $gender
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
                    $translation->setGender($gender);
                    $translation->setContent($content);
                    if ($meta !== null) {
                        $translation->setMeta($meta);
                    }
                    
                    // Also set the ID fields for backward compatibility
                    $translation->setIdSections($section->getId());
                    $translation->setIdFields($field->getId());
                    $translation->setIdLanguages($language->getId());
                    $translation->setIdGenders($gender->getId());
                    
                    $this->entityManager->persist($translation);
                }
            }
        }
        
        $this->entityManager->flush();
    }
}
