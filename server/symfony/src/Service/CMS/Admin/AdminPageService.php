<?php

namespace App\Service\CMS\Admin;

use App\Entity\Lookup;
use App\Entity\Page;
use App\Entity\PageType;
use App\Exception\ServiceException;
use App\Repository\LookupRepository;
use App\Repository\PageRepository;
use App\Repository\PageTypeRepository;
use App\Repository\SectionRepository;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Service\Core\LookupService;
use App\Service\Core\UserContextAwareService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service for handling page-related operations in the admin panel
 */
class AdminPageService extends UserContextAwareService
{
    /************************* START ADMIN PAGES *************************/
    /**
     * CMS select page keyword
     */
    private const CMS_SELECT_PAGE_KEYWORD = 'cmsSelect';

    // ACL group name constants
    private const GROUP_ADMIN = 'admin';
    private const GROUP_SUBJECT = 'subject';
    private const GROUP_THERAPIST = 'therapist';

    /************************* END ADMIN PAGES *************************/

    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly LookupRepository $lookupRepository,
        private readonly PageTypeRepository $pageTypeRepository,
        private readonly ManagerRegistry $doctrine,
        ACLService $aclService,
        UserContextService $userContextService
    ) {
        parent::__construct($userContextService, $aclService);
    }

    /**
     * Get page fields
     * 
     * @param string $pageKeyword The page keyword
     * @return array The page fields
     * @throws ServiceException If page not found or access denied
     */
    public function getPageFields(string $pageKeyword): array
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);

        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check if user has access to the page
        if (!$this->hasAccess($page->getId(), 'select')) {
            $this->throwForbidden('Access denied');
        }

        // Return raw data - no wrapping in API response structure
        return [
            'fields' => [], // Future implementation will populate this
            'page_id' => $page->getId(),
            'page_keyword' => $page->getKeyword()
        ];
    }

    /**
     * Get page sections
     * 
     * @param string $pageKeyword The page keyword
     * @return array The page sections in a hierarchical structure
     * @throws \Exception If page not found
     */
    public function getPageSections(string $pageKeyword): array
    {
        // Check if user has admin access to cms
        if (!$this->hasAccess($this->pageRepository->findOneBy(['keyword' => self::CMS_SELECT_PAGE_KEYWORD])->getId(), 'select')) {
            $this->throwForbidden('Access denied');
        }

        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check if user has access to the page
        if (!$this->hasAccess($page->getId(), 'select')) {
            $this->throwForbidden('Access denied');
        }

        // Call stored procedure for hierarchical sections
        $flatSections = $this->sectionRepository->fetchSectionsHierarchicalByPageId($page->getId());
        return $this->buildNestedSections($flatSections);
    }

    /** Private methods */
    /**
     * @brief Transforms a flat array of sections into a nested hierarchical structure
     * 
     * @param array $sections Flat array of section objects with level and path properties
     * @return array Nested array with children properly nested under their parents
     */
    private function buildNestedSections(array $sections): array
    {
        // Create a map of sections by ID for quick lookup
        $sectionsById = [];
        $rootSections = [];

        // First pass: index all sections by ID
        foreach ($sections as $section) {
            $section['children'] = [];
            $sectionsById[$section['id']] = $section;
        }

        // Second pass: build the hierarchy
        foreach ($sections as $section) {
            $id = $section['id'];

            // If it's a root section (level 0), add to root array
            if ($section['level'] === 0) {
                $rootSections[] = &$sectionsById[$id];
            } else {
                // Find parent using the path
                $pathParts = explode(',', $section['path']);
                if (count($pathParts) >= 2) {
                    $parentId = (int)$pathParts[count($pathParts) - 2];

                    // If parent exists, add this as its child
                    if (isset($sectionsById[$parentId])) {
                        $sectionsById[$parentId]['children'][] = &$sectionsById[$id];
                    }
                }
            }
        }

        return $rootSections;
    }
    
    /**
     * Create a new page
     * 
     * @param string $keyword Unique keyword for the page
     * @param string $pageAccessTypeCode Code of the page access type lookup
     * @param bool $isHeadless Whether the page is headless
     * @param bool $isOpenAccess Whether the page has open access
     * @param string|null $url URL for the page
     * @param int|null $navPosition Navigation position
     * @param int|null $footerPosition Footer position
     * @param int|null $parentId ID of the parent page
     * 
     * @return Page The created page entity
     * @throws ServiceException If validation fails or required entities not found
     */
    public function createPage(
        string $keyword,
        string $pageAccessTypeCode,
        bool $isHeadless = false,
        bool $isOpenAccess = false,
        ?string $url = null,
        ?int $navPosition = null,
        ?int $footerPosition = null,
        ?int $parentId = null,
    ): Page {   
        
        // Check if keyword already exists
        if ($this->pageRepository->findOneBy(['keyword' => $keyword])) {
            $this->throwConflict("Page with keyword '{$keyword}' already exists");
        }

        // Check if url already exists
        if ($this->pageRepository->findOneBy(['url' => $url])) {
            $this->throwConflict("Page with url '{$url}' already exists");
        }
        
        // Get page access type by code
        $pageAccessType = $this->lookupRepository->findOneBy([
            'typeCode' => 'pageAccessTypes',
            'lookupCode' => $pageAccessTypeCode
        ]);
        if (!$pageAccessType) {
            $this->throwNotFound("Page access type with code '{$pageAccessTypeCode}' not found");
        }
        
        // Get parent page if provided
        $parentPage = null;
        if ($parentId) {
            $parentPage = $this->pageRepository->find($parentId);
            if (!$parentPage) {
                $this->throwNotFound("Parent page with ID {$parentId} not found");
            }
        }
        
        // Get action if provided
        $actionCode = LookupService::PAGE_ACTIONS_SECTIONS;
        $action = $this->lookupRepository->findOneBy([
            'typeCode' => 'pageActions',
            'lookupCode' => $actionCode
        ]);
        if (!$action) {
            $this->throwNotFound("Action with code '{$actionCode}' not found");
        }
        
        // Get default page type (experiment)
        $pageType = $this->pageTypeRepository->findOneBy(['name' => 'experiment']);
        if (!$pageType) {
            $this->throwNotFound("Default page type 'experiment' not found");
        }
        
        // Create new page entity
        $page = new Page();
        $page->setKeyword($keyword);
        $page->setPageAccessType($pageAccessType);
        $page->setIsHeadless($isHeadless);
        $page->setIsOpenAccess($isOpenAccess);
        $page->setUrl($url);
        $page->setNavPosition($navPosition);
        $page->setFooterPosition($footerPosition);
        $page->setParentPage($parentPage);
        $page->setPageType($pageType);
        $page->setIsSystem(false);
        $page->setAction($action);

        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->persist($page);
            $this->entityManager->flush(); // To get the page ID

            // Fetch groups by name
            $groupRepo = $this->entityManager->getRepository(\App\Entity\Group::class);
            $adminGroup = $groupRepo->findOneBy(['name' => self::GROUP_ADMIN]);
            $subjectGroup = $groupRepo->findOneBy(['name' => self::GROUP_SUBJECT]);
            $therapistGroup = $groupRepo->findOneBy(['name' => self::GROUP_THERAPIST]);
            if (!$adminGroup || !$subjectGroup || !$therapistGroup) {
                throw new ServiceException('One or more required groups not found.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // ACL for admin group (full access)
            $this->aclService->addGroupAcl($page, $adminGroup, true, true, true, true, $this->entityManager);

            // ACL for subject group (select only)
            $this->aclService->addGroupAcl($page, $subjectGroup, true, false, false, false, $this->entityManager);

            // ACL for therapist group (select only)
            $this->aclService->addGroupAcl($page, $therapistGroup, true, false, false, false, $this->entityManager);

            // ACL for creating user (full access)
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) {
                throw new ServiceException('Current user not found.', Response::HTTP_UNAUTHORIZED);
            }
            $this->aclService->addUserAcl($page, $currentUser, true, true, true, true, $this->entityManager);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e instanceof ServiceException ? $e : new ServiceException('Failed to create page and assign ACLs: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, $e);
        }
        return $page;
    }
}
