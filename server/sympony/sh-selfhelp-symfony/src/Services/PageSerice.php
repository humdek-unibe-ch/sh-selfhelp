<?php

namespace App\Service;

use App\Entity\Page;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

/**
 * Page service
 * 
 * Handles page-related operations
 */
class PageService
{
    /**
     * Constructor
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly Security $security,
        private readonly ACLService $aclService
    ) {
    }

    /**
     * Get page fields
     * 
     * @param string $pageKeyword The page keyword
     * @return array The page fields
     * @throws AccessDeniedException If user doesn't have access to the page
     * @throws \Exception If page not found
     */
    public function getPageFields(string $pageKeyword): array
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        
        if (!$page) {
            throw new \Exception('Page not found');
        }
        
        // Check if user has access to the page
        if (!$this->aclService->hasAccess($this->security->getUser()->getId(), $page->getId(), 'select')) {
            throw new AccessDeniedException('Access denied');
        }
        
        // Get page fields
        $fields = [];
        foreach ($page->getPageFields() as $pageField) {
            $fieldName = $pageField->getField()->getName();
            $languageId = $pageField->getLanguage()->getId();
            
            if (!isset($fields[$fieldName])) {
                $fields[$fieldName] = [];
            }
            
            $fields[$fieldName][$languageId] = [
                'id' => $pageField->getField()->getId(),
                'content' => $pageField->getContent()
            ];
        }
        
        return [
            'page' => [
                'fields' => $fields,
                'page_id' => $page->getId(),
                'page_keyword' => $page->getKeyword()
            ]
        ];
    }
    
    /**
     * Get page sections
     * 
     * @param string $pageKeyword The page keyword
     * @return array The page sections in a hierarchical structure
     * @throws AccessDeniedException If user doesn't have access to the page
     * @throws \Exception If page not found
     */
    public function getPageSections(string $pageKeyword): array
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        
        if (!$page) {
            throw new \Exception('Page not found');
        }
        
        // Check if user has access to the page
        if (!$this->aclService->hasAccess($this->security->getUser()->getId(), $page->getId(), 'select')) {
            throw new AccessDeniedException('Access denied');
        }
        
        // Get hierarchical sections
        return $this->sectionRepository->findHierarchicalSections($page->getId());
    }
}