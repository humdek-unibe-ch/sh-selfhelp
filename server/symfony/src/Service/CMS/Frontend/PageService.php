<?php

namespace App\Service\CMS\Frontend;

use App\Exception\ServiceException;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Repository\LookupRepository;
use App\Service\Auth\UserContextService;
use App\Service\ACL\ACLService;
use App\Service\Core\LookupTypes;
use App\Service\Core\UserContextAwareService;

class PageService extends UserContextAwareService
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly LookupRepository $lookupRepository,
        UserContextService $userContextService,
        ?ACLService $aclService = null
    ) {
        parent::__construct($userContextService, $aclService);
    }

    /**
     * Get all published pages for the current user, filtered by mode and ACL
     *
     * @param string $mode Either 'web' or 'mobile'
     * @return array
     */
    public function getAllAccessiblePagesForUser(string $mode): array
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            throw new ServiceException('User not authenticated');
        }

        // Call stored procedure to get all pages with ACL for the user
        $conn = $this->pageRepository->getEntityManager()->getConnection();
        $sql = "CALL get_user_acl(:uid, -1)";
        $allPages = $conn->executeQuery($sql, ['uid' => $user->getId()])->fetchAllAssociative();

        // Determine which type to remove based on mode
        $removeType = $mode === LookupTypes::PAGE_ACCESS_TYPES_MOBILE ? LookupTypes::PAGE_ACCESS_TYPES_WEB : LookupTypes::PAGE_ACCESS_TYPES_MOBILE;
        $removeTypeId = $this->lookupRepository->getLookupIdByCode(LookupTypes::PAGE_ACCESS_TYPES, $removeType);
        $sectionsTypeId = $this->lookupRepository->getLookupIdByCode(LookupTypes::PAGE_ACTIONS, LookupTypes::PAGE_ACTIONS_SECTIONS);        

        // Filter pages
        $pages = array_values(array_filter($allPages, function ($item) use ($removeTypeId, $sectionsTypeId) {
            return $item['id_pageAccessTypes'] != $removeTypeId &&
                $item['acl_select'] == 1 &&
                $item['id_actions'] == $sectionsTypeId &&
                in_array($item['id_type'], ['2', '3', '4']) &&
                $item['url'] != '';
        }));

        return $pages;
    }
}
