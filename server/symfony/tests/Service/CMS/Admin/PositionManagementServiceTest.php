<?php

namespace App\Tests\Service\CMS\Admin;

use App\Entity\PagesSection;
use App\Entity\SectionsHierarchy;
use App\Entity\Page;
use App\Service\CMS\Admin\PositionManagementService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityRepository;

class PositionManagementServiceTest extends TestCase
{
    private $entityManager;
    private $positionManagementService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->positionManagementService = new PositionManagementService(
            $this->entityManager
        );
    }

    /**
     * Test normalizePageSectionPositions method
     */
    public function testNormalizePageSectionPositions(): void
    {
        // Create mock repository
        $pagesSectionRepository = $this->createMock(EntityRepository::class);
        
        // Create mock page sections
        $pageSection1 = $this->createMock(PagesSection::class);
        $pageSection2 = $this->createMock(PagesSection::class);
        $pageSection3 = $this->createMock(PagesSection::class);
        
        $pageSections = [$pageSection1, $pageSection2, $pageSection3];
        
        // Configure mocks
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(PagesSection::class)
            ->willReturn($pagesSectionRepository);
            
        $pagesSectionRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['page' => 1], ['position' => 'ASC', 'idSections' => 'ASC'])
            ->willReturn($pageSections);
            
        // Expect positions to be set in increments of 10
        $pageSection1
            ->expects($this->once())
            ->method('setPosition')
            ->with(0);
            
        $pageSection2
            ->expects($this->once())
            ->method('setPosition')
            ->with(10);
            
        $pageSection3
            ->expects($this->once())
            ->method('setPosition')
            ->with(20);
            
        // Expect flush to be called when flush=true
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
            
        // Call the method
        $this->positionManagementService->normalizePageSectionPositions(1, true);
    }
    
    /**
     * Test normalizePageSectionPositions method without flush
     */
    public function testNormalizePageSectionPositionsWithoutFlush(): void
    {
        // Create mock repository
        $pagesSectionRepository = $this->createMock(EntityRepository::class);
        
        // Create mock page sections
        $pageSection1 = $this->createMock(PagesSection::class);
        $pageSection2 = $this->createMock(PagesSection::class);
        
        $pageSections = [$pageSection1, $pageSection2];
        
        // Configure mocks
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(PagesSection::class)
            ->willReturn($pagesSectionRepository);
            
        $pagesSectionRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['page' => 1], ['position' => 'ASC', 'idSections' => 'ASC'])
            ->willReturn($pageSections);
            
        // Expect positions to be set in increments of 10
        $pageSection1
            ->expects($this->once())
            ->method('setPosition')
            ->with(0);
            
        $pageSection2
            ->expects($this->once())
            ->method('setPosition')
            ->with(10);
            
        // Expect flush NOT to be called when flush=false
        $this->entityManager
            ->expects($this->never())
            ->method('flush');
            
        // Call the method
        $this->positionManagementService->normalizePageSectionPositions(1, false);
    }
    
    /**
     * Test normalizeSectionHierarchyPositions method
     */
    public function testNormalizeSectionHierarchyPositions(): void
    {
        // Create mock repository
        $sectionsHierarchyRepository = $this->createMock(EntityRepository::class);
        
        // Create mock section hierarchies
        $sectionHierarchy1 = $this->createMock(SectionsHierarchy::class);
        $sectionHierarchy2 = $this->createMock(SectionsHierarchy::class);
        
        $sectionHierarchy1
            ->method('getPosition')
            ->willReturn(5);
            
        $sectionHierarchy2
            ->method('getPosition')
            ->willReturn(10);
        
        $sectionHierarchies = [$sectionHierarchy1, $sectionHierarchy2];
        
        // Configure mocks
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(SectionsHierarchy::class)
            ->willReturn($sectionsHierarchyRepository);
            
        $sectionsHierarchyRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['parentSection' => 1], ['position' => 'ASC', 'childSection' => 'ASC'])
            ->willReturn($sectionHierarchies);
            
        // Expect positions to be set in increments of 10
        $sectionHierarchy1
            ->expects($this->once())
            ->method('setPosition')
            ->with(0);
            
        $sectionHierarchy2
            ->expects($this->once())
            ->method('setPosition')
            ->with(10);
            
        // Expect flush to be called when flush=true
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
            
        // Call the method
        $this->positionManagementService->normalizeSectionHierarchyPositions(1, true);
    }
    
    /**
     * Test reorderPagePositions method
     */
    public function testReorderPagePositions(): void
    {
        // Create mock pages
        $page1 = $this->createMock(Page::class);
        $page2 = $this->createMock(Page::class);
        $page3 = $this->createMock(Page::class);
        
        $page1
            ->method('getId')
            ->willReturn(1);
            
        $page2
            ->method('getId')
            ->willReturn(2);
            
        $page3
            ->method('getId')
            ->willReturn(3);
        
        $pages = [$page1, $page2, $page3];
        
        // Create mock query builder
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        
        // Configure mocks
        $this->entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())->method('select')->with('p')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('from')->with('App\\Entity\\Page', 'p')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('where')->with('p.navPosition IS NOT NULL')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('andWhere')->with('p.parentPage = :parentId')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('setParameter')->with('parentId', 1)->willReturnSelf();
        $queryBuilder->expects($this->once())->method('orderBy')->with('p.navPosition', 'ASC')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('addOrderBy')->with('p.id', 'ASC')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('getQuery')->willReturn($query);
        $query->expects($this->once())->method('getResult')->willReturn($pages);
            
        // Expect positions to be set in increments of 10
        $page1
            ->expects($this->once())
            ->method('setNavPosition')
            ->with(10);
            
        $page2
            ->expects($this->once())
            ->method('setNavPosition')
            ->with(20);
            
        $page3
            ->expects($this->once())
            ->method('setNavPosition')
            ->with(30);
            
        // Expect flush to be called when flush=true
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
            
        // Call the method
        $this->positionManagementService->reorderPagePositions(1, 'nav', true);
    }
    
    /**
     * Test reorderPagePositions method with footer position type
     */
    public function testReorderPagePositionsWithFooterType(): void
    {
        // Create mock pages
        $page1 = $this->createMock(Page::class);
        $page2 = $this->createMock(Page::class);
        
        $page1
            ->method('getId')
            ->willReturn(1);
            
        $page2
            ->method('getId')
            ->willReturn(2);
        
        $pages = [$page1, $page2];
        
        // Create mock query builder
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        
        // Configure mocks
        $this->entityManager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
            
        $queryBuilder->expects($this->once())->method('select')->with('p')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('from')->with('App\\Entity\\Page', 'p')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('where')->with('p.footerPosition IS NOT NULL')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('andWhere')->with('p.parentPage = :parentId')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('setParameter')->with('parentId', 1)->willReturnSelf();
        $queryBuilder->expects($this->once())->method('orderBy')->with('p.footerPosition', 'ASC')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('addOrderBy')->with('p.id', 'ASC')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('getQuery')->willReturn($query);
        $query->expects($this->once())->method('getResult')->willReturn($pages);
            
        // Expect positions to be set in increments of 10
        $page1
            ->expects($this->once())
            ->method('setFooterPosition')
            ->with(10);
            
        $page2
            ->expects($this->once())
            ->method('setFooterPosition')
            ->with(20);
            
        // Expect flush to be called when flush=true
        $this->entityManager
            ->expects($this->once())
            ->method('flush');
            
        // Call the method
        $this->positionManagementService->reorderPagePositions(1, 'footer', true);
    }
}
