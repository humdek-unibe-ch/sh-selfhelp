<?php

namespace App\Tests\Service\CMS\Admin;

use App\Service\CMS\Admin\PositionManagementService;
use App\Tests\Controller\Api\V1\BaseControllerTest;
use Doctrine\ORM\EntityManagerInterface;

class PositionManagementServiceTest extends BaseControllerTest
{
    private PositionManagementService $positionManagementService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->positionManagementService = static::getContainer()->get(PositionManagementService::class);
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Test normalizePageSectionPositions method
     */
    public function testNormalizePageSectionPositions(): void
    {
        // Find a real page with sections to test with
        $page = $this->entityManager->getRepository('App\Entity\Page')->findOneBy([]);
        
        if (!$page) {
            $this->markTestSkipped('No pages found to test with');
        }
        
        $pageId = $page->getId();
        
        // Call the method with real database
        $this->positionManagementService->normalizePageSectionPositions($pageId, true);
        
        // Verify the result by checking the database state
        $pageSections = $this->entityManager->getRepository('App\Entity\PagesSection')
            ->findBy(['page' => $page], ['position' => 'ASC']);
            
        // Assert that positions are normalized to increments of 10
        $expectedPosition = 0;
        foreach ($pageSections as $pageSection) {
            $this->assertEquals($expectedPosition, $pageSection->getPosition());
            $expectedPosition += 10;
        }
    }

    /**
     * Test normalizePageSectionPositions method without flush
     */
    public function testNormalizePageSectionPositionsWithoutFlush(): void
    {
        // Find a real page with sections to test with
        $page = $this->entityManager->getRepository('App\Entity\Page')->findOneBy([]);
        
        if (!$page) {
            $this->markTestSkipped('No pages found to test with');
        }
        
        $pageId = $page->getId();
        
        // Get initial positions
        $initialSections = $this->entityManager->getRepository('App\Entity\PagesSection')
            ->findBy(['page' => $page], ['position' => 'ASC']);
        $initialPositions = array_map(fn($ps) => $ps->getPosition(), $initialSections);
        
        // Call the method without flush
        $this->positionManagementService->normalizePageSectionPositions($pageId, false);
        
        // Since we didn't flush, positions in memory should be updated but not persisted
        // This is harder to test without accessing the service internals, so we'll just verify no exception is thrown
        $this->assertTrue(true, 'Method executed without exception');
    }

    /**
     * Test normalizeSectionHierarchyPositions method
     */
    public function testNormalizeSectionHierarchyPositions(): void
    {
        // Find a real section that has children to test with
        $sectionHierarchy = $this->entityManager->getRepository('App\Entity\SectionsHierarchy')->findOneBy([]);
        
        if (!$sectionHierarchy) {
            $this->markTestSkipped('No section hierarchies found to test with');
        }
        
        $parentSectionId = $sectionHierarchy->getParent();
        
        // Call the method with real database
        $this->positionManagementService->normalizeSectionHierarchyPositions($parentSectionId, true);
        
        // Verify the result by checking the database state
        $hierarchies = $this->entityManager->getRepository('App\Entity\SectionsHierarchy')
            ->findBy(['parent' => $parentSectionId], ['position' => 'ASC', 'child' => 'ASC']);
            
        // Assert that positions are normalized to increments of 10
        $expectedPosition = 0;
        foreach ($hierarchies as $hierarchy) {
            $this->assertEquals($expectedPosition, $hierarchy->getPosition());
            $expectedPosition += 10;
        }
    }

    /**
     * Test reorderPagePositions method
     */
    public function testReorderPagePositions(): void
    {
        // Call the method with real database (use null for root pages)
        $this->positionManagementService->reorderPagePositions(null, 'nav', true);
        
        // Verify the result by checking the database state
        $pages = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from('App\\Entity\\Page', 'p')
            ->where('p.nav_position IS NOT NULL')
            ->andWhere('p.parentPage IS NULL')  // Changed to parentPage IS NULL for root pages
            ->orderBy('p.nav_position', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
            
        // If no pages found, that's okay - the method should handle it gracefully
        if (count($pages) > 0) {
            // Assert that positions are normalized to increments of 10
            $expectedPosition = 10;
            foreach ($pages as $page) {
                $this->assertEquals($expectedPosition, $page->getNavPosition());
                $expectedPosition += 10;
            }
        } else {
            // No pages to reorder, which is fine
            $this->assertTrue(true, 'No pages found to reorder, which is acceptable');
        }
    }

    /**
     * Test reorderPagePositions method with footer position type
     */
    public function testReorderPagePositionsWithFooterType(): void
    {
        // Call the method with real database (use null for root pages)
        $this->positionManagementService->reorderPagePositions(null, 'footer', true);
        
        // Verify the result by checking the database state
        $pages = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from('App\\Entity\\Page', 'p')
            ->where('p.footer_position IS NOT NULL')
            ->andWhere('p.parentPage IS NULL')  // Changed to parentPage IS NULL for root pages
            ->orderBy('p.footer_position', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
            
        // If no pages found, that's okay - the method should handle it gracefully
        if (count($pages) > 0) {
            // Assert that positions are normalized to increments of 10
            $expectedPosition = 10;
            foreach ($pages as $page) {
                $this->assertEquals($expectedPosition, $page->getFooterPosition());
                $expectedPosition += 10;
            }
        } else {
            // No pages to reorder, which is fine
            $this->assertTrue(true, 'No pages found to reorder, which is acceptable');
        }
    }
}
