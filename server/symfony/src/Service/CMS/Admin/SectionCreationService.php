<?php

namespace App\Service\CMS\Admin;

use App\Entity\Section;
use App\Entity\PagesSection;
use App\Entity\SectionsHierarchy;
use App\Exception\ServiceException;
use App\Service\CMS\DataTableService;
use App\Service\Core\UserContextAwareService;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Cache\Core\CacheService;
use App\Service\Cache\Core\CacheInvalidationService;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Repository\StyleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling section creation operations
 */
class SectionCreationService extends UserContextAwareService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StyleRepository $styleRepository,
        private readonly PositionManagementService $positionManagementService,
        private readonly DataTableService $dataTableService,
        private readonly CacheService $cacheService,
        ACLService $aclService,
        UserContextService $userContextService,
        PageRepository $pageRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct($userContextService, $aclService, $pageRepository, $sectionRepository);
    }

    /**
     * Creates a new section with the specified style and adds it to a page
     *
     * @param string $pageKeyword The keyword of the page to add the section to
     * @param int $styleId The ID of the style to use for the section
     * @param int|null $position The position of the section on the page
     * @return array The ID and position of the new section
     * @throws ServiceException If the page or style is not found
     */
    public function createPageSection(string $pageKeyword, int $styleId, ?int $position): array
    {
        // Permission check
        $this->checkAccess($pageKeyword, 'update');
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
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
            $this->entityManager->persist($section);
            $this->entityManager->flush(); // Flush to get the section ID

            // Add the section to the page
            $pagesSection = new PagesSection();
            $pagesSection->setPage($page);
            $pagesSection->setSection($section);
            $pagesSection->setPosition($position);
            $this->entityManager->persist($pagesSection);
            $this->entityManager->flush();

            // Auto-create dataTable if this is a form section
            if ($this->dataTableService->isFormSection($section)) {
                $this->dataTableService->createDataTableForFormSection($section);
            }

            $this->positionManagementService->normalizePageSectionPositions($page->getId(), true);

            // Invalidate page and section caches
            $this->cacheInvalidationService->invalidatePage($page, 'update');
            $this->cacheInvalidationService->invalidateSection($section, 'create');

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
     * @param string|null $pageKeyword The page keyword.
     * @param int $parentSectionId The ID of the parent section
     * @param int $styleId The ID of the style to use for the section
     * @param int|null $position The position of the child section
     * @return array The ID and position of the new section
     * @throws ServiceException If the parent section or style is not found
     */
    public function createChildSection(?string $pageKeyword, int $parentSectionId, int $styleId, ?int $position): array
    {
        $parentSection = $this->sectionRepository->find($parentSectionId);
        if (!$parentSection) {
            $this->throwNotFound('Parent section not found');
        }
        
        // If page_keyword is not provided, find it from the parent section
        if ($pageKeyword === null) {
            // Get the page from the parent section by finding which page this section belongs to
            $pageSection = $this->entityManager->getRepository(PagesSection::class)
                ->findOneBy(['section' => $parentSectionId]);
            
            if ($pageSection) {
                $page = $pageSection->getPage();
                if ($page) {
                    $pageKeyword = $page->getKeyword();
                }
            }
            
            if (!$pageKeyword) {
                $this->throwNotFound('Page not found for parent section');
            }
        }
        
        // Permission check
        $this->checkAccess($pageKeyword, 'update');
        $this->checkSectionInPage($pageKeyword, $parentSectionId);
        
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
            $this->entityManager->persist($childSection);
            $this->entityManager->flush(); // Flush to get the section ID

            // Add the child section to the parent section
            $sectionHierarchy = new SectionsHierarchy();
            $sectionHierarchy->setParentSection($parentSection);
            $sectionHierarchy->setChildSection($childSection);
            $sectionHierarchy->setPosition($position);

             // Auto-create dataTable if this is a form section
             if ($this->dataTableService->isFormSection($childSection)) {
                $this->dataTableService->createDataTableForFormSection($childSection);
             }

            $this->entityManager->persist($sectionHierarchy);
            $this->entityManager->flush();
            $this->positionManagementService->normalizeSectionHierarchyPositions($parentSectionId, true);
            
            // Invalidate section caches
            $this->cacheInvalidationService->invalidateSection($parentSection, 'update');
            $this->cacheInvalidationService->invalidateSection($childSection, 'create');
            
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
} 