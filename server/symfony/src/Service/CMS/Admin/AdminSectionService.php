<?php

namespace App\Service\CMS\Admin;

use App\Entity\Section;
use App\Entity\SectionsHierarchy;
use App\Entity\PagesSection;
use App\Exception\ServiceException;
use App\Repository\SectionRepository;
use App\Repository\StyleRepository;
use App\Repository\PageRepository;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Core\TransactionService;
use App\Service\Core\UserContextAwareService;
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
        private readonly SectionRepository $sectionRepository,
        private readonly TransactionService $transactionService,
        private readonly StyleRepository $styleRepository,
        private readonly PageRepository $pageRepository,
        ACLService $aclService,
        UserContextService $userContextService
    ) {
        parent::__construct($userContextService, $aclService);
    }

    /**
     * Get a section by its ID with its fields and translations
     * @param int $section_id
     * @return array
     * @throws ServiceException If section not found or access denied
     */
    public function getSection(int $section_id): array
    {
        // Fetch section
        $section = $this->sectionRepository->find($section_id);
        if (!$section) {
            $this->throwNotFound('Section not found');
        }

        // Permission check
        if (!$this->hasAccess($section->getId(), 'select')) {
            $this->throwForbidden('Access denied');
        }

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
        ];
    }

    /**
     * Get all children sections for a parent section
     * @param int $parent_section_id
     * @return array
     */
    public function getChildrenSections(int $parent_section_id): array
    {
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
        
        // Adjust as needed for project conventions
        return [
            'id' => $section->getId(),
            'name' => $section->getName(),
            'style' => $styleData
            // Add more fields as needed
        ];
    }

    /**
     * Adds a child section to a parent section.
     *
     * @param int $parent_section_id The ID of the parent section.
     * @param int $child_section_id The ID of the child section.
     * @param int|null $position The desired position.
     * @param string|null $oldParentPageId The ID of the old parent page to remove the relationship from (optional).
     * @param int|null $oldParentSectionId The ID of the old parent section to remove the relationship from (optional).
     * @return SectionsHierarchy The new section hierarchy relationship.
     * @throws ServiceException If the relationship already exists or entities are not found.
     */
    public function addSectionToSection(int $parent_section_id, int $child_section_id, ?int $position, ?string $oldParentPageId = null, ?int $oldParentSectionId = null): SectionsHierarchy
    {
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
            $this->normalizeSectionHierarchyPositions($parent_section_id);
            $this->entityManager->commit();
            return $sectionHierarchy;

            $this->normalizeSectionHierarchyPositions($parent_section_id);

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
     * @param int $parent_section_id The ID of the parent section.
     * @param int $child_section_id The ID of the child section.
     * @throws ServiceException If the relationship does not exist.
     */
    public function removeSectionFromSection(int $parent_section_id, int $child_section_id): void
    {
        $this->entityManager->beginTransaction();
        try {
            $sectionHierarchy = $this->entityManager->getRepository(SectionsHierarchy::class)
                ->findOneBy(['parentSection' => $parent_section_id, 'childSection' => $child_section_id]);
            if (!$sectionHierarchy) {
                $this->throwNotFound('Section hierarchy relationship not found.');
            }

            $this->entityManager->remove($sectionHierarchy);
            $this->entityManager->flush();

            $this->normalizeSectionHierarchyPositions($parent_section_id);

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
     * @param int $section_id The ID of the section to delete.
     * @throws ServiceException If the section is not found.
     */
    public function deleteSection(int $section_id): void
    {
        $this->entityManager->beginTransaction();
        try {
            $section = $this->sectionRepository->find($section_id);
            if (!$section) {
                $this->throwNotFound('Section not found');
            }

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
     * @return int The ID of the new section
     * @throws ServiceException If the page or style is not found
     */
    public function createPageSection(string $page_keyword, int $styleId, ?int $position): int
    {
        $this->entityManager->beginTransaction();
        try {
            $page = $this->pageRepository->findOneBy(['keyword' => $page_keyword]);
            if (!$page) {
                $this->throwNotFound('Page not found');
            }

            if (!$this->hasAccess($page->getId(), 'update')) {
                $this->throwForbidden('Access denied to modify this page');
            }

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

            $this->normalizePageSectionPositions($page->getId());

            $this->entityManager->commit();
            return $section->getId();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to create section on page: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Creates a new section with the specified style and adds it as a child to another section
     *
     * @param int $parent_section_id The ID of the parent section
     * @param int $styleId The ID of the style to use for the section
     * @param int|null $position The position of the child section
     * @return int The ID of the new section
     * @throws ServiceException If the parent section or style is not found
     */
    public function createChildSection(int $parent_section_id, int $styleId, ?int $position): int
    {
        $this->entityManager->beginTransaction();
        try {
            $parentSection = $this->sectionRepository->find($parent_section_id);
            if (!$parentSection) {
                $this->throwNotFound('Parent section not found');
            }

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

            $this->normalizeSectionHierarchyPositions($parent_section_id);

            $this->entityManager->commit();
            return $childSection->getId();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to create child section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Normalizes the positions of sections on a page.
     * 
     * @param int $pageId The ID of the page to normalize section positions for
     */
    private function normalizePageSectionPositions(int $pageId): void
    {
        $pagesSections = $this->entityManager->getRepository(PagesSection::class)
            ->findBy(['page' => $pageId], ['position' => 'ASC']);
        
        // Reindex positions starting from 0
        $position = 0;
        foreach ($pagesSections as $pagesSection) {
            $pagesSection->setPosition($position++);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Normalizes the positions of all child sections within a specific parent section.
     */
    private function normalizeSectionHierarchyPositions(int $parent_section_id): void
    {
        $sectionHierarchies = $this->entityManager->getRepository(SectionsHierarchy::class)->findBy(
            ['parentSection' => $parent_section_id],
            ['position' => 'ASC', 'childSection' => 'ASC']
        );

        // Sort by position, but if a section was just moved, use its new position
        usort($sectionHierarchies, function ($a, $b) {
            return ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0);
        });

        $currentPosition = 0;
        foreach ($sectionHierarchies as $sectionHierarchy) {
            $sectionHierarchy->setPosition($currentPosition);
            $currentPosition += 10;
        }
    }

}
