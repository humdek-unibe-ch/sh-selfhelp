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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling section-related operations in the admin panel
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

        // Get style and its fields
        $style = $section->getStyle();
        if (!$style) {
            return [
                'section' => $this->normalizeSection($section),
                'fields' => [],
                'languages' => []
            ];
        }

        // Get all StylesField for this style
        $stylesFields = $style->getStylesFields();
        
        // Fetch all field translations for this section
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t, l, f, g, ft')
            ->from('App\Entity\SectionsFieldsTranslation', 't')
            ->leftJoin('t.language', 'l')
            ->leftJoin('t.field', 'f')
            ->leftJoin('f.type', 'ft')
            ->leftJoin('t.gender', 'g')
            ->where('t.section = :section')
            ->setParameter('section', $section);
        $translations = $qb->getQuery()->getResult();

        // Group translations by field and language
        $translationsByFieldLang = [];
        $languages = [];
        foreach ($translations as $tr) {
            $fieldId = $tr->getField() ? $tr->getField()->getId() : $tr->getIdFields();
            $langId = $tr->getLanguage() ? $tr->getLanguage()->getId() : $tr->getIdLanguages();
            $genderId = $tr->getGender() ? $tr->getGender()->getId() : $tr->getIdGenders();
            if (!isset($translationsByFieldLang[$fieldId])) {
                $translationsByFieldLang[$fieldId] = [];
            }
            if (!isset($translationsByFieldLang[$fieldId][$langId])) {
                $translationsByFieldLang[$fieldId][$langId] = [];
            }
            $translationsByFieldLang[$fieldId][$langId][$genderId] = [
                'content' => $tr->getContent(),
                'meta' => $tr->getMeta(),
            ];
            // Collect unique languages
            if ($tr->getLanguage()) {
                $languages[$langId] = [
                    'id' => $langId,
                    'locale' => $tr->getLanguage()->getLocale(),
                ];
            }
        }
        $languages = array_values($languages);

        // Format fields with translations
        $formattedFields = [];
        foreach ($stylesFields as $stylesField) {
            $field = $stylesField->getField();
            if (!$field) continue;
            
            $fieldId = $field->getId();
            
            $fieldData = [
                'id' => $fieldId,
                'name' => $field->getName(),
                'type' => $field->getType() ? $field->getType()->getName() : null,
                'default_value' => $stylesField->getDefaultValue(),
                'help' => $stylesField->getHelp(),
                'disabled' => $stylesField->isDisabled(),
                'hidden' => $stylesField->getHidden(),
                'display' => $field->isDisplay(),
                'translations' => []
            ];
            
            // Handle translations based on display flag
            if ($field->isDisplay()) {
                // Content field (display=1) - can have translations for each language
                if (isset($translationsByFieldLang[$fieldId])) {
                    foreach ($translationsByFieldLang[$fieldId] as $langId => $genderTranslations) {
                        foreach ($genderTranslations as $genderId => $translation) {
                            $language = isset($languages[$langId-1]) ? $languages[$langId-1] : null;
                            $fieldData['translations'][] = [
                                'language_id' => $langId,
                                'language_code' => $language ? $language['locale'] : null,
                                'gender_id' => $genderId,
                                'content' => $translation['content'],
                                'meta' => $translation['meta']
                            ];
                        }
                    }
                }
            } else {
                // Property field (display=0) - use language_id = 1 only
                if (isset($translationsByFieldLang[$fieldId][1])) {
                    $propertyTranslation = $translationsByFieldLang[$fieldId][1][1] ?? null;
                    if ($propertyTranslation) {
                        $fieldData['translations'][] = [
                            'language_id' => 1,
                            'language_code' => 'property',  // This is a property, not actually language-specific
                            'gender_id' => 1,
                            'content' => $propertyTranslation['content'],
                            'meta' => $propertyTranslation['meta']
                        ];
                    }
                }
            }
            
            $formattedFields[] = $fieldData;
        }

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
     * @param string|null $oldParentPageId The ID of the old parent page to remove the relationship from (optional).
     * @param int|null $oldParentSectionId The ID of the old parent section to remove the relationship from (optional).
     * @return SectionsHierarchy The new section hierarchy relationship.
     * @throws ServiceException If the relationship already exists or entities are not found.
     */
    public function addSectionToSection(string $page_keyword, int $parent_section_id, int $child_section_id, ?int $position, ?string $oldParentPageId = null, ?int $oldParentSectionId = null): SectionsHierarchy
    {
        // Permission check
        $this->checkAccess($page_keyword, 'update');
        $this->checkSectionInPage($page_keyword,$parent_section_id);
        $this->entityManager->beginTransaction();
        try {
            $parentSection = $this->sectionRepository->find($parent_section_id);
            if (!$parentSection) {
                $this->throwNotFound('Parent section not found');
            }

            $childSection = $this->sectionRepository->find($child_section_id);
            if (!$childSection) {
                $this->throwNotFound('Child section not found');
            }

            //remove old parent page relationship
            if ($oldParentPageId !== null) {
                $oldParentPage = $this->pageRepository->find($oldParentPageId);
                if ($oldParentPage) {
                    $oldRelationship = $this->entityManager->getRepository(PagesSection::class)->findOneBy([
                        'page' => $oldParentPage,
                        'section' => $childSection
                    ]);
                    if ($oldRelationship) {
                        $this->entityManager->remove($oldRelationship);
                        $this->entityManager->flush();
                    }
                }
            }

            //remove old parent section relationship
            if ($oldParentSectionId !== null) {
                $oldParentSection = $this->sectionRepository->find($oldParentSectionId);
                if ($oldParentSection) {
                    $oldRelationship = $this->entityManager->getRepository(SectionsHierarchy::class)->findOneBy([
                        'parentSection' => $oldParentSection,
                        'childSection' => $childSection
                    ]);
                    if ($oldRelationship) {
                        $this->entityManager->remove($oldRelationship);
                        $this->entityManager->flush();
                    }
                }
            }

            $sectionHierarchy = new SectionsHierarchy();
            $sectionHierarchy->setParentSection($parentSection);
            $sectionHierarchy->setParent($parentSection->getId());
            $sectionHierarchy->setChildSection($childSection);
            $sectionHierarchy->setChild($childSection->getId());
            $sectionHierarchy->setPosition($position);
            $this->entityManager->persist($sectionHierarchy);
            $this->entityManager->flush();
            $this->positionManagementService->normalizeSectionHierarchyPositions($parent_section_id, true);
            $this->entityManager->commit();
            return $sectionHierarchy;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to add section to section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
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
        // Permission check
        $this->checkAccess($page_keyword, 'update');
        $this->checkSectionInPage($page_keyword,$parent_section_id);
        $this->entityManager->beginTransaction();
        try {
            $sectionHierarchy = $this->entityManager->getRepository(SectionsHierarchy::class)
                ->findOneBy(['parentSection' => $parent_section_id, 'childSection' => $child_section_id]);
            if (!$sectionHierarchy) {
                $this->throwNotFound('Section hierarchy relationship not found.');
            }

            $this->entityManager->remove($sectionHierarchy);
            $this->entityManager->flush();
            $this->positionManagementService->normalizeSectionHierarchyPositions($parent_section_id, true);
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to remove section from section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
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
        $this->checkAccess($page_keyword, 'update');
        
        // For child sections, we need to check if they belong to the page hierarchy
        // instead of directly checking if they're in the page
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        
        // Check if this section belongs to the page (either directly or as a child)
        // First, check if it's directly associated with the page
        $directPageSection = $this->entityManager->getRepository(PagesSection::class)
            ->findOneBy(['page' => $page, 'section' => $section_id]);
        
        $belongsToPage = false;
        if ($directPageSection) {
            $belongsToPage = true;
        } else {
            // If not directly associated, check if it's a child of a section that belongs to this page
            $hierarchyEntry = $this->entityManager->getRepository(SectionsHierarchy::class)
                ->findOneBy(['childSection' => $section]);
            
            if ($hierarchyEntry) {
                $parentSection = $hierarchyEntry->getParentSection();
                if ($parentSection) {
                    // Recursively check if the parent section belongs to the page
                    $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
                    $sectionIds = array_map(function($section) {
                        return is_array($section) && isset($section['id']) ? (string)$section['id'] : null;
                    }, $flatSections);
                    
                    if (in_array((string)$parentSection->getId(), $sectionIds, true)) {
                        $belongsToPage = true;
                    }
                }
            }
        }
        
        if (!$belongsToPage) {
            // Debug: Include what sections we found in the error message
            $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
            $sectionIds = array_map(function($section) {
                return is_array($section) && isset($section['id']) ? (string)$section['id'] : null;
            }, $flatSections);
            $foundSections = implode(', ', array_filter($sectionIds));
            $this->throwForbidden("Section $section_id is not associated with page {$page->getKeyword()} (ID: {$page->getId()}). Found sections: [$foundSections]");
        }
        
        $this->entityManager->beginTransaction();
        try {
            // Remove from pages_sections
            $pagesSections = $this->entityManager->getRepository(PagesSection::class)->findBy(['section' => $section]);
            foreach ($pagesSections as $pagesSection) {
                $this->entityManager->remove($pagesSection);
            }

            // Remove from sections_hierarchy as parent
            $hierarchiesAsParent = $this->entityManager->getRepository(SectionsHierarchy::class)->findBy(['parentSection' => $section]);
            foreach ($hierarchiesAsParent as $hierarchy) {
                $this->entityManager->remove($hierarchy);
            }

            // Remove from sections_hierarchy as child
            $hierarchiesAsChild = $this->entityManager->getRepository(SectionsHierarchy::class)->findBy(['childSection' => $section]);
            foreach ($hierarchiesAsChild as $hierarchy) {
                $this->entityManager->remove($hierarchy);
            }

            // Finally remove the section itself
            $this->entityManager->remove($section);
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to delete section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
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
        // Permission check
        $this->checkAccess($page_keyword, 'update');
        $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        $this->entityManager->beginTransaction();
        try {
            $style = $this->styleRepository->find($styleId);
            if (!$style) {
                $this->throwNotFound('Style not found');
            }

            // Create a new section with the specified style
            $section = new Section();
            $section->setName(time() . '-' . $style->getName());
            $section->setStyle($style);
            $section->setIdStyles($style->getId());
            $this->entityManager->persist($section);
            $this->entityManager->flush(); // Flush to get the section ID

            // Add the section to the page
            $pagesSection = new PagesSection();
            $pagesSection->setPage($page);
            $pagesSection->setSection($section);
            $pagesSection->setPosition($position);
            $pagesSection->setIdPages($page->getId());
            $pagesSection->setIdSections($section->getId());
            $this->entityManager->persist($pagesSection);
            $this->entityManager->flush();

            $this->positionManagementService->normalizePageSectionPositions($page->getId(), true);

            $this->entityManager->commit();
            return [
                'id' => $section->getId(),
                'position' => $pagesSection->getPosition(),
            ];
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to create section on page: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
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
        $parentSection = $this->sectionRepository->find($parent_section_id);
        if (!$parentSection) {
            $this->throwNotFound('Parent section not found');
        }
        
        // If page_keyword is not provided, find it from the parent section
        if ($page_keyword === null) {
            // Get the page from the parent section by finding which page this section belongs to
            $pageSection = $this->entityManager->getRepository(PagesSection::class)
                ->findOneBy(['section' => $parent_section_id]);
            
            if ($pageSection) {
                $page = $pageSection->getPage();
                if ($page) {
                    $page_keyword = $page->getKeyword();
                }
            }
            
            if (!$page_keyword) {
                $this->throwNotFound('Page not found for parent section');
            }
        }
        
        // Permission check
        $this->checkAccess($page_keyword, 'update');
        $this->checkSectionInPage($page_keyword, $parent_section_id);
        
        $this->entityManager->beginTransaction();
        try {
            $style = $this->styleRepository->find($styleId);
            if (!$style) {
                $this->throwNotFound('Style not found');
            }

            // Create a new section with the specified style
            $childSection = new Section();
            $childSection->setName(time() . '-' . $style->getName());
            $childSection->setStyle($style);
            $childSection->setIdStyles($style->getId());
            $this->entityManager->persist($childSection);
            $this->entityManager->flush(); // Flush to get the section ID

            // Add the child section to the parent section
            $sectionHierarchy = new SectionsHierarchy();
            $sectionHierarchy->setParentSection($parentSection);
            $sectionHierarchy->setParent($parentSection->getId());
            $sectionHierarchy->setChildSection($childSection);
            $sectionHierarchy->setChild($childSection->getId());
            $sectionHierarchy->setPosition($position);
            $this->entityManager->persist($sectionHierarchy);
            $this->entityManager->flush();
            $this->positionManagementService->normalizeSectionHierarchyPositions($parent_section_id, true);
            $this->entityManager->commit();
            return [
                'id' => $childSection->getId(),
                'position' => $sectionHierarchy->getPosition(),
            ];
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to create child section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
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

            // Validate that all fields belong to the section's style
            $allFieldIds = array_merge(
                array_column($contentFields, 'fieldId'),
                array_column($propertyFields, 'fieldId')
            );
            
            if (!empty($allFieldIds)) {
                $validFieldIds = $this->entityManager->getRepository(\App\Entity\StylesField::class)
                    ->createQueryBuilder('sf')
                    ->select('IDENTITY(sf.field)')
                    ->where('sf.style = :styleId')
                    ->andWhere('sf.field IN (:fieldIds)')
                    ->setParameter('styleId', $section->getIdStyles())
                    ->setParameter('fieldIds', $allFieldIds)
                    ->getQuery()
                    ->getScalarResult();
                
                $validFieldIds = array_column($validFieldIds, 1); // Extract field IDs from result
                $invalidFieldIds = array_diff($allFieldIds, $validFieldIds);
                
                if (!empty($invalidFieldIds)) {
                    throw new ServiceException(
                        sprintf("Fields [%s] do not belong to style %d", 
                            implode(', ', $invalidFieldIds), 
                            $section->getIdStyles()
                        ),
                        Response::HTTP_BAD_REQUEST
                    );
                }
            }

            // Update content field translations (display=1 fields)
            foreach ($contentFields as $field) {
                $fieldId = $field['fieldId'];
                $languageId = $field['languageId'];
                $content = $field['value'];

                // For content fields, we use gender_id = 1 as default
                $genderId = 1;

                // Check if translation exists
                $existingTranslation = $this->entityManager->getRepository(SectionsFieldsTranslation::class)
                    ->findOneBy([
                        'idSections' => $section->getId(),
                        'idFields' => $fieldId,
                        'idLanguages' => $languageId,
                        'idGenders' => $genderId
                    ]);

                if ($existingTranslation) {
                    // Update existing translation
                    $existingTranslation->setContent($content);
                } else {
                    // Create new translation
                    $newTranslation = new SectionsFieldsTranslation();
                    $newTranslation->setIdSections($section->getId());
                    $newTranslation->setIdFields($fieldId);
                    $newTranslation->setIdLanguages($languageId);
                    $newTranslation->setIdGenders($genderId);
                    $newTranslation->setContent($content);

                    // Also set the entity relationships
                    $newTranslation->setSection($section);

                    // Get the Field entity
                    $fieldEntity = $this->entityManager->getRepository(\App\Entity\Field::class)->find($fieldId);
                    if ($fieldEntity) {
                        $newTranslation->setField($fieldEntity);
                    }

                    // Get the Language entity
                    $language = $this->entityManager->getRepository(\App\Entity\Language::class)->find($languageId);
                    if ($language) {
                        $newTranslation->setLanguage($language);
                    }

                    // Get the Gender entity
                    $gender = $this->entityManager->getRepository(\App\Entity\Gender::class)->find($genderId);
                    if ($gender) {
                        $newTranslation->setGender($gender);
                    }

                    $this->entityManager->persist($newTranslation);
                }
            }

            // Update property field translations (display=0 fields)
            foreach ($propertyFields as $field) {
                $fieldId = $field['fieldId'];
                $content = is_bool($field['value']) ? ($field['value'] ? '1' : '0') : (string) $field['value'];

                // For property fields, we use language_id = 1 and gender_id = 1
                $languageId = 1;
                $genderId = 1;

                // Check if translation exists
                $existingTranslation = $this->entityManager->getRepository(SectionsFieldsTranslation::class)
                    ->findOneBy([
                        'idSections' => $section->getId(),
                        'idFields' => $fieldId,
                        'idLanguages' => $languageId,
                        'idGenders' => $genderId
                    ]);

                if ($existingTranslation) {
                    // Update existing translation
                    $existingTranslation->setContent($content);
                } else {
                    // Create new translation
                    $newTranslation = new SectionsFieldsTranslation();
                    $newTranslation->setIdSections($section->getId());
                    $newTranslation->setIdFields($fieldId);
                    $newTranslation->setIdLanguages($languageId);
                    $newTranslation->setIdGenders($genderId);
                    $newTranslation->setContent($content);

                    // Also set the entity relationships
                    $newTranslation->setSection($section);

                    // Get the Field entity
                    $fieldEntity = $this->entityManager->getRepository(\App\Entity\Field::class)->find($fieldId);
                    if ($fieldEntity) {
                        $newTranslation->setField($fieldEntity);
                    }

                    // Get the Language entity (always language ID 1 for properties)
                    $language = $this->entityManager->getRepository(\App\Entity\Language::class)->find($languageId);
                    if ($language) {
                        $newTranslation->setLanguage($language);
                    }

                    // Get the Gender entity (always gender ID 1 for properties)
                    $gender = $this->entityManager->getRepository(\App\Entity\Gender::class)->find($genderId);
                    if ($gender) {
                        $newTranslation->setGender($gender);
                    }

                    $this->entityManager->persist($newTranslation);
                }
            }

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
        
        // Get all sections for this page
        $pageSections = $this->entityManager->getRepository(PagesSection::class)
            ->findBy(['page' => $page]);
        
        if (empty($pageSections)) {
            return [];
        }
        
        // Extract section IDs
        $sectionIds = array_map(function($pageSection) {
            return $pageSection->getSection()->getId();
        }, $pageSections);
        
        // Build section hierarchy
        $sectionsData = $this->buildSectionsExportData($sectionIds);
        
        return $sectionsData;
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
        
        // Get all child sections recursively
        $childSections = $this->getAllChildSections($section_id);
        $sectionIds = array_merge([$section_id], $childSections);
        
        // Build section export data
        $sectionsData = $this->buildSectionsExportData($sectionIds);
        
        return $sectionsData;
    }
    
    /**
     * Get all child section IDs recursively for a given section
     * 
     * @param int $sectionId The ID of the parent section
     * @return array Array of child section IDs
     */
    private function getAllChildSections(int $sectionId): array
    {
        $childIds = [];
        
        // Get direct children
        $hierarchies = $this->entityManager->getRepository(SectionsHierarchy::class)
            ->findBy(['parentSection' => $sectionId]);
        
        foreach ($hierarchies as $hierarchy) {
            $childId = $hierarchy->getChildSection()->getId();
            $childIds[] = $childId;
            
            // Recursively get children of this child
            $grandChildIds = $this->getAllChildSections($childId);
            $childIds = array_merge($childIds, $grandChildIds);
        }
        
        return $childIds;
    }
    
    /**
     * Build export data for sections
     * 
     * @param array $sectionIds Array of section IDs to export
     * @return array JSON-serializable array with sections data
     */
    private function buildSectionsExportData(array $sectionIds): array
    {
        if (empty($sectionIds)) {
            return [];
        }
        
        $sectionsData = [];
        
        foreach ($sectionIds as $sectionId) {
            $section = $this->sectionRepository->find($sectionId);
            if (!$section) {
                continue;
            }
            
            // Get section data
            $sectionData = [
                'name' => $section->getName(),
                'style_name' => $section->getStyle() ? $section->getStyle()->getName() : null,
                'fields' => [],
                'children' => []
            ];
            
            // Get section fields and translations
            $translations = $this->entityManager->getRepository(SectionsFieldsTranslation::class)
                ->findBy(['section' => $section]);
            
            foreach ($translations as $translation) {
                $field = $translation->getField();
                $language = $translation->getLanguage();
                $gender = $translation->getGender();
                
                if (!$field || !$language) {
                    continue;
                }
                
                $fieldName = $field->getName();
                $locale = $language->getLocale();
                $genderCode = $gender ? $gender->getName() : 'default';
                
                // Skip fields without names
                if (empty($fieldName)) {
                    continue;
                }
                
                // Group fields by name, locale, and gender
                if (!isset($sectionData['fields'][$fieldName])) {
                    $sectionData['fields'][$fieldName] = [
                        'type' => $field->getType() ? $field->getType()->getName() : null,
                        'translations' => []
                    ];
                }
                
                $sectionData['fields'][$fieldName]['translations'][] = [
                    'locale' => $locale,
                    'gender' => $genderCode,
                    'content' => $translation->getContent()
                ];
            }
            
            // Get child sections
            $hierarchies = $this->entityManager->getRepository(SectionsHierarchy::class)
                ->findBy(['parentSection' => $section]);
            
            $childSectionIds = [];
            foreach ($hierarchies as $hierarchy) {
                $childSectionIds[] = $hierarchy->getChildSection()->getId();
            }
            
            // Add section data to result
            $sectionsData[] = $sectionData;
        }
        
        return $sectionsData;
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
        $position = 1;
        
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
            
            // Import fields and translations
            if (isset($sectionData['fields']) && is_array($sectionData['fields'])) {
                $this->importSectionFields($section, $sectionData['fields']);
            }
            
            // Add section to page or parent section
            if ($page) {
                // Add to page
                $pageSection = new PagesSection();
                $pageSection->setPage($page);
                $pageSection->setSection($section);
                $pageSection->setPosition($position++);
                
                $this->entityManager->persist($pageSection);
            } elseif ($parentSection) {
                // Add to parent section
                $sectionHierarchy = new SectionsHierarchy();
                $sectionHierarchy->setParentSection($parentSection);
                $sectionHierarchy->setChildSection($section);
                $sectionHierarchy->setPosition($position++);
                
                $this->entityManager->persist($sectionHierarchy);
            }
            
            $this->entityManager->flush();
            
            // Record the imported section
            $importedSections[] = [
                'id' => $section->getId(),
                'name' => $section->getName(),
                'style_name' => $styleName,
                'position' => $position - 1
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
     * Import section fields and translations
     * 
     * @param Section $section The section to import fields for
     * @param array $fieldsData The fields data to import
     */
    private function importSectionFields(Section $section, array $fieldsData): void
    {
        foreach ($fieldsData as $fieldName => $fieldData) {
            // Find or create field
            $field = $this->entityManager->getRepository(Field::class)
                ->findOneBy(['name' => $fieldName]);
            
            if (!$field) {
                // Create new field if it doesn't exist
                $field = new Field();
                $field->setName($fieldName);
                
                // Set field type if provided
                if (isset($fieldData['type'])) {
                    $fieldType = $this->entityManager->getRepository(FieldType::class)
                        ->findOneBy(['name' => $fieldData['type']]);
                    
                    if ($fieldType) {
                        $field->setType($fieldType);
                    }
                }
                
                $this->entityManager->persist($field);
                $this->entityManager->flush();
            }
            
            // Import translations
            if (isset($fieldData['translations']) && is_array($fieldData['translations'])) {
                foreach ($fieldData['translations'] as $translationData) {
                    $locale = $translationData['locale'] ?? null;
                    $genderCode = $translationData['gender'] ?? 'default';
                    $content = $translationData['content'] ?? '';
                    
                    if (!$locale) {
                        continue;
                    }
                    
                    // Find language by locale
                    $language = $this->entityManager->getRepository(\App\Entity\Language::class)
                        ->findOneBy(['locale' => $locale]);
                    
                    if (!$language) {
                        // Skip translations for languages that don't exist
                        continue;
                    }
                    
                    // Find gender by name
                    $gender = $this->entityManager->getRepository(\App\Entity\Gender::class)
                        ->findOneBy(['name' => $genderCode]);
                    
                    if (!$gender) {
                        // Use default gender if not found
                        $gender = $this->entityManager->getRepository(\App\Entity\Gender::class)
                            ->find(1);
                    }
                    
                    // Create translation
                    $translation = new SectionsFieldsTranslation();
                    $translation->setSection($section);
                    $translation->setField($field);
                    $translation->setLanguage($language);
                    $translation->setGender($gender);
                    $translation->setContent($content);
                    
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
