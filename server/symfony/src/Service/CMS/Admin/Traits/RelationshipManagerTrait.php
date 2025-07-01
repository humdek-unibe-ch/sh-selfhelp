<?php

namespace App\Service\CMS\Admin\Traits;

use App\Entity\Page;
use App\Entity\Section;
use App\Entity\PagesSection;
use App\Entity\SectionsHierarchy;
use App\Exception\ServiceException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait for managing entity relationships in admin services
 */
trait RelationshipManagerTrait
{
    /**
     * Remove old parent relationships when moving sections
     * 
     * @param int|null $oldParentPageId
     * @param int|null $oldParentSectionId
     * @param Section $childSection
     * @param EntityManagerInterface $entityManager
     */
    protected function removeOldParentRelationships(?int $oldParentPageId, ?int $oldParentSectionId, Section $childSection, EntityManagerInterface $entityManager): void
    {
        // Remove old parent page relationship
        if ($oldParentPageId !== null) {
            $oldParentPage = $entityManager->getRepository(Page::class)->find($oldParentPageId);
            if ($oldParentPage) {
                $oldRelationship = $entityManager->getRepository(PagesSection::class)->findOneBy([
                    'page' => $oldParentPage,
                    'section' => $childSection
                ]);
                if ($oldRelationship) {
                    $entityManager->remove($oldRelationship);
                }
            }
        }

        // Remove old parent section relationship
        if ($oldParentSectionId !== null) {
            $oldParentSection = $entityManager->getRepository(Section::class)->find($oldParentSectionId);
            if ($oldParentSection) {
                $oldRelationship = $entityManager->getRepository(SectionsHierarchy::class)->findOneBy([
                    'parentSection' => $oldParentSection,
                    'childSection' => $childSection
                ]);
                if ($oldRelationship) {
                    $entityManager->remove($oldRelationship);
                }
            }
        }
    }

    /**
     * Create or update page-section relationship
     * 
     * @param Page $page
     * @param Section $section
     * @param int|null $position
     * @param EntityManagerInterface $entityManager
     * @return PagesSection
     */
    protected function createOrUpdatePageSectionRelationship(Page $page, Section $section, ?int $position, EntityManagerInterface $entityManager): PagesSection
    {
        // Check for existing relationship
        $existing = $entityManager->getRepository(PagesSection::class)
            ->findOneBy(['page' => $page, 'section' => $section]);
        
        if ($existing) {
            // Update existing relationship
            $existing->setPosition($position);
            return $existing;
        }

        // Create new relationship
        $pageSection = new PagesSection();
        $pageSection->setPage($page);
        $pageSection->setSection($section);
        $pageSection->setPosition($position);
        
        $entityManager->persist($pageSection);
        return $pageSection;
    }

    /**
     * Create or update section hierarchy relationship
     * 
     * @param Section $parentSection
     * @param Section $childSection
     * @param int|null $position
     * @param EntityManagerInterface $entityManager
     * @return SectionsHierarchy
     */
    protected function createSectionHierarchyRelationship(Section $parentSection, Section $childSection, ?int $position, EntityManagerInterface $entityManager): SectionsHierarchy
    {
        // Check for existing relationship
        $existing = $entityManager->getRepository(SectionsHierarchy::class)
            ->findOneBy(['parentSection' => $parentSection, 'childSection' => $childSection]);
        
        if ($existing) {
            // Update existing relationship
            $existing->setPosition($position);
            return $existing;
        }

        // Create new relationship
        $sectionHierarchy = new SectionsHierarchy();
        $sectionHierarchy->setParentSection($parentSection);
        $sectionHierarchy->setChildSection($childSection);
        $sectionHierarchy->setPosition($position);
        
        $entityManager->persist($sectionHierarchy);
        return $sectionHierarchy;
    }

    /**
     * Remove all section relationships (used when deleting sections)
     * 
     * @param Section $section
     * @param EntityManagerInterface $entityManager
     */
    protected function removeAllSectionRelationships(Section $section, EntityManagerInterface $entityManager): void
    {
        // Remove from pages_sections
        $pagesSections = $entityManager->getRepository(PagesSection::class)->findBy(['section' => $section]);
        foreach ($pagesSections as $pagesSection) {
            $entityManager->remove($pagesSection);
        }

        // Remove from sections_hierarchy as parent
        $hierarchiesAsParent = $entityManager->getRepository(SectionsHierarchy::class)->findBy(['parentSection' => $section]);
        foreach ($hierarchiesAsParent as $hierarchy) {
            $entityManager->remove($hierarchy);
        }

        // Remove from sections_hierarchy as child
        $hierarchiesAsChild = $entityManager->getRepository(SectionsHierarchy::class)->findBy(['childSection' => $section]);
        foreach ($hierarchiesAsChild as $hierarchy) {
            $entityManager->remove($hierarchy);
        }
    }

    /**
     * Check if section belongs to page hierarchy
     * 
     * @param Page $page
     * @param int $sectionId
     * @param EntityManagerInterface $entityManager
     * @param \App\Repository\SectionRepository $sectionRepository
     * @return bool
     */
    protected function sectionBelongsToPageHierarchy(Page $page, int $sectionId, EntityManagerInterface $entityManager, $sectionRepository): bool
    {
        // Check if it's directly associated with the page
        $directPageSection = $entityManager->getRepository(PagesSection::class)
            ->findOneBy(['page' => $page, 'section' => $sectionId]);
        
        if ($directPageSection) {
            return true;
        }

        // Check if it's a child of a section that belongs to this page
        $flatSections = $sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        $sectionIds = array_map(function($section) {
            return is_array($section) && isset($section['id']) ? (string)$section['id'] : null;
        }, $flatSections);
        
        return in_array((string)$sectionId, $sectionIds, true);
    }
} 