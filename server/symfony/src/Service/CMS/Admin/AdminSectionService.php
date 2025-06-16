<?php

namespace App\Service\CMS\Admin;

use App\Entity\SectionsHierarchy;
use App\Entity\Section;
use App\Entity\PagesSection;
use App\Exception\ServiceException;
use App\Repository\SectionRepository;
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
        ACLService $aclService,
        UserContextService $userContextService
    ) {
        parent::__construct($userContextService, $aclService);
    }

    /**
     * Adds a child section to a parent section.
     *
     * @param int $parentSectionId The ID of the parent section.
     * @param int $childSectionId The ID of the child section.
     * @param int|null $position The desired position.
     * @return SectionsHierarchy The new section hierarchy relationship.
     * @throws ServiceException If the relationship already exists or entities are not found.
     */
    public function addSectionToSection(int $parentSectionId, int $childSectionId, ?int $position): SectionsHierarchy
    {
        $this->entityManager->beginTransaction();
        try {
            $parentSection = $this->sectionRepository->find($parentSectionId);
            if (!$parentSection) {
                $this->throwNotFound('Parent section not found');
            }

            $childSection = $this->sectionRepository->find($childSectionId);
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

            $this->normalizeSectionHierarchyPositions($parentSectionId);

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
     * @param int $parentSectionId The ID of the parent section.
     * @param int $childSectionId The ID of the child section.
     * @param int|null $position The new position.
     * @return SectionsHierarchy The updated section hierarchy relationship.
     * @throws ServiceException If the relationship does not exist.
     */
    public function updateSectionInSection(int $parentSectionId, int $childSectionId, ?int $position): SectionsHierarchy
    {
        $this->entityManager->beginTransaction();
        try {
            $sectionHierarchy = $this->entityManager->getRepository(SectionsHierarchy::class)->findOneBy(['parentSection' => $parentSectionId, 'childSection' => $childSectionId]);
            if (!$sectionHierarchy) {
                $this->throwNotFound('Section hierarchy relationship not found. Use the add endpoint to create it.');
            }

            $sectionHierarchy->setPosition($position);
            $this->entityManager->flush();

            $this->normalizeSectionHierarchyPositions($parentSectionId);

            $this->entityManager->commit();
            return $sectionHierarchy;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to update section in section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Removes a child section from a parent section.
     *
     * @param int $parentSectionId The ID of the parent section.
     * @param int $childSectionId The ID of the child section.
     * @throws ServiceException If the relationship does not exist.
     */
    public function removeSectionFromSection(int $parentSectionId, int $childSectionId): void
    {
        $this->entityManager->beginTransaction();
        try {
            $sectionHierarchy = $this->entityManager->getRepository(SectionsHierarchy::class)->findOneBy(['parentSection' => $parentSectionId, 'childSection' => $childSectionId]);
            if (!$sectionHierarchy) {
                $this->throwNotFound('Section hierarchy relationship not found.');
            }

            $this->entityManager->remove($sectionHierarchy);
            $this->entityManager->flush();

            $this->normalizeSectionHierarchyPositions($parentSectionId);

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
     * @param int $sectionId The ID of the section to delete.
     * @throws ServiceException If the section is not found.
     */
    public function deleteSection(int $sectionId): void
    {
        $this->entityManager->beginTransaction();
        try {
            $section = $this->sectionRepository->find($sectionId);
            if (!$section) {
                $this->throwNotFound('Section not found');
            }

            // Remove all hierarchy relationships where this section is a child
            $childOf = $this->entityManager->getRepository(SectionsHierarchy::class)->findBy(['childSection' => $sectionId]);
            foreach ($childOf as $relation) {
                $this->entityManager->remove($relation);
            }

            // Remove all hierarchy relationships where this section is a parent
            $parentOf = $this->entityManager->getRepository(SectionsHierarchy::class)->findBy(['parentSection' => $sectionId]);
            foreach ($parentOf as $relation) {
                $this->entityManager->remove($relation);
            }

            // Remove all page-section relationships
            $pageSections = $this->entityManager->getRepository(PagesSection::class)->findBy(['section' => $sectionId]);
            foreach ($pageSections as $pageSection) {
                $this->entityManager->remove($pageSection);
            }
            
            $this->entityManager->flush(); // Flush removals of relationships first

            // Finally, remove the section itself
            $this->entityManager->remove($section);
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to delete section: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, ['previous' => $e]);
        }
    }

    /**
     * Normalizes the positions of all child sections within a specific parent section.
     */
    private function normalizeSectionHierarchyPositions(int $parentSectionId): void
    {
        $sectionHierarchies = $this->entityManager->getRepository(SectionsHierarchy::class)->findBy(
            ['parentSection' => $parentSectionId],
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
