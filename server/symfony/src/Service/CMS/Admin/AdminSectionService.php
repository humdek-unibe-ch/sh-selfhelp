<?php

namespace App\Service\CMS\Admin;

use App\Entity\SectionsHierarchy;
use App\Entity\Section;
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
     * Adds a child section to a parent section.
     *
     * @param int $parent_section_id The ID of the parent section.
     * @param int $child_section_id The ID of the child section.
     * @param int|null $position The desired position.
     * @return SectionsHierarchy The new section hierarchy relationship.
     * @throws ServiceException If the relationship already exists or entities are not found.
     */
    public function addSectionToSection(int $parent_section_id, int $child_section_id, ?int $position): SectionsHierarchy
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

            $existing = $this->entityManager->getRepository(SectionsHierarchy::class)->findOneBy(['parentSection' => $parentSection, 'childSection' => $childSection]);
            if ($existing) {
                throw new ServiceException('Child section already exists in this parent. Use the update endpoint to change its position.', Response::HTTP_CONFLICT);
            }

            $sectionHierarchy = new SectionsHierarchy();
            $sectionHierarchy->setParentSection($parentSection);
            $sectionHierarchy->setChildSection($childSection);
            $sectionHierarchy->setPosition($position);
            $this->entityManager->persist($sectionHierarchy);
            $this->entityManager->flush();

            $this->normalizeSectionHierarchyPositions($parent_section_id);

            $this->entityManager->commit();
            return $sectionHierarchy;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to add section to section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Updates the position of a child section within a parent section.
     *
     * @param int $parent_section_id The ID of the parent section.
     * @param int $child_section_id The ID of the child section.
     * @param int|null $position The new position.
     * @throws ServiceException If the relationship does not exist.
     */
    public function updateSectionInSection(int $parent_section_id, int $child_section_id, ?int $position): void
    {
        $this->entityManager->beginTransaction();
        try {
            $sectionHierarchy = $this->entityManager->getRepository(SectionsHierarchy::class)->findOneBy(['parentSection' => $parent_section_id, 'childSection' => $child_section_id]);
            if (!$sectionHierarchy) {
                $this->throwNotFound('Section hierarchy relationship not found. Use the add endpoint to create it.');
            }

            $sectionHierarchy->setPosition($position);
            $this->entityManager->flush();

            $this->normalizeSectionHierarchyPositions($parent_section_id);

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to update section in section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
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
            $sectionHierarchy->setChildSection($childSection);
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

        $currentPosition = 10;
        foreach ($sectionHierarchies as $sectionHierarchy) {
            $sectionHierarchy->setPosition($currentPosition);
            $currentPosition += 10;
        }

        $this->entityManager->flush();
    }

}
