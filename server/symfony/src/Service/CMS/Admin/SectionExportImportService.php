<?php

namespace App\Service\CMS\Admin;

use App\Entity\Language;
use App\Entity\Page;
use App\Entity\Section;
use App\Entity\PagesSection;
use App\Entity\SectionsHierarchy;
use App\Entity\SectionsFieldsTranslation;
use App\Entity\Field;
use App\Exception\ServiceException;
use App\Service\Core\UserContextAwareService;
use App\Service\Core\TransactionService;
use App\Service\Core\LookupService;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Cache\Core\CacheService;
use App\Service\CMS\Common\SectionUtilityService;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Repository\StyleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling section export/import operations
 */
class SectionExportImportService extends UserContextAwareService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SectionUtilityService $sectionUtilityService,
        private readonly StyleRepository $styleRepository,
        private readonly TransactionService $transactionService,
        private readonly CacheService $cacheService,
        ACLService $aclService,
        UserContextService $userContextService,
        PageRepository $pageRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct($userContextService, $aclService, $pageRepository, $sectionRepository);
    }

    /**
     * Export all sections of a given page (including all nested sections) as JSON
     * 
     * @param string $pageKeyword The keyword of the page to export sections from
     * @return array JSON-serializable array with all page sections
     * @throws ServiceException If page not found or access denied
     */
    public function exportPageSections(string $pageKeyword): array
    {
        // Permission check
        $this->checkAccess($pageKeyword, 'select');
        
        // Get the page
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Use existing hierarchical fetching method
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        
        if (empty($flatSections)) {
            return [];
        }
        
        // Build hierarchical structure using existing utility method
        $hierarchicalSections = $this->sectionUtilityService->buildNestedSections($flatSections,false);
        
        // Add field translations to the hierarchical structure
        $this->addFieldTranslationsToSections($hierarchicalSections);
        
        return $hierarchicalSections;
    }
    
    /**
     * Export a selected section (and all of its nested children) as JSON
     * 
     * @param string $pageKeyword The keyword of the page containing the section
     * @param int $sectionId The ID of the section to export
     * @return array JSON-serializable array with the section and its children
     * @throws ServiceException If section not found or access denied
     */
    public function exportSection(string $pageKeyword, int $sectionId): array
    {
        // Permission check
        $this->checkAccess($pageKeyword, 'select');
        $this->checkSectionInPage($pageKeyword, $sectionId);
        
        // Get the section
        $section = $this->sectionRepository->find($sectionId);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }
        
        // Get the page to use existing hierarchical method
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Get all sections for the page using existing method
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        
        // Build hierarchical structure
        $hierarchicalSections = $this->sectionUtilityService->buildNestedSections($flatSections,false);
        
        // Find the specific section and its subtree
        $targetSection = $this->findSectionInHierarchy($hierarchicalSections, $sectionId);
        
        if (!$targetSection) {
            $this->throwNotFound('Section not found in page hierarchy');
        }
        
        // Add field translations to the section subtree
        $targetSections = [$targetSection];
        $this->addFieldTranslationsToSections($targetSections);
        
        return $targetSections;
    }
    
    /**
     * Import sections from JSON into a target page
     * 
     * @param string $pageKeyword The keyword of the target page
     * @param array $sectionsData The sections data to import
     * @param int|null $position The position where the sections should be inserted
     * @return array Result of the import operation
     * @throws ServiceException If page not found or access denied
     */
    public function importSectionsToPage(string $pageKeyword, array $sectionsData, ?int $position = null): array
    {
        // Permission check
        $this->checkAccess($pageKeyword, 'update');
        
        // Get the page
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Start transaction
        $this->entityManager->beginTransaction();
        
        try {
            $importedSections = $this->importSections($sectionsData, $page, null, $position);
            
            // Invalidate page and sections cache after import
            $this->cacheService->invalidatePage($page, 'update');
            $this->cacheService->invalidateCategory(CacheService::CATEGORY_SECTIONS);
            
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
     * @param string $pageKeyword The keyword of the target page
     * @param int $parentSectionId The ID of the parent section to import into
     * @param array $sectionsData The sections data to import
     * @param int|null $position The position where the sections should be inserted
     * @return array Result of the import operation
     * @throws ServiceException If section not found or access denied
     */
    public function importSectionsToSection(string $pageKeyword, int $parentSectionId, array $sectionsData, ?int $position = null): array
    {
        // Permission check
        $this->checkAccess($pageKeyword, 'update');
        $this->checkSectionInPage($pageKeyword, $parentSectionId);
        
        // Get the parent section
        $parentSection = $this->sectionRepository->find($parentSectionId);
        if (!$parentSection) {
            $this->throwNotFound('Parent section not found');
        }
        
        // Start transaction
        $this->entityManager->beginTransaction();
        
        try {
            $importedSections = $this->importSections($sectionsData, null, $parentSection, $position);
            
            // Invalidate sections cache after import
            $this->cacheService->invalidateSection($parentSection, 'update');
            $this->cacheService->invalidateCategory(CacheService::CATEGORY_SECTIONS);
            
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
     * Find a section in hierarchical structure recursively
     * 
     * @param array $sections Hierarchical sections array
     * @param int $sectionId The section ID to find
     * @return array|null The found section with its children, or null if not found
     */
    private function findSectionInHierarchy(array $sections, int $sectionId): ?array
    {
        foreach ($sections as $section) {
            if ($section['id'] == $sectionId) {
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
                'fields' => (object)[]
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
            $cleanSection['fields'] = empty($fields) ? (object)[] : $fields;
            
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
     * Only processes field names with their values - minimal data needed
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