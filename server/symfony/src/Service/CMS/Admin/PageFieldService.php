<?php

namespace App\Service\CMS\Admin;

use App\Entity\Page;
use App\Entity\PageTypeField;
use App\Entity\PagesFieldsTranslation;
use App\Exception\ServiceException;
use App\Service\CMS\Admin\Traits\TranslationManagerTrait;
use App\Service\CMS\Admin\Traits\FieldValidatorTrait;
use App\Service\Core\BaseService;
use App\Service\ACL\ACLService;
use App\Service\Cache\Core\CacheService;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use App\Service\Core\UserContextAwareService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service for handling page field operations
 */
class PageFieldService extends BaseService
{
    use TranslationManagerTrait;
    use FieldValidatorTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheService $cache,
        private readonly ACLService $aclService,
        private readonly PageRepository $pageRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly UserContextAwareService $userContextAwareService
    ) {
    }

    /**
     * Get page with its fields and translations
     * 
     * @param string $pageKeyword The page keyword
     * @return array The page with its fields and translations
     * @throws ServiceException If page not found or access denied
     */
    public function getPageWithFields(string $pageKeyword): array
    {
        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);
        if (!$page) {
            $this->throwNotFound('Page not found');
        }
        // Try to get from cache first
        $cacheKey = "page_with_fields_{$page->getId()}";

        return $this->cache
            ->withCategory(CacheService::CATEGORY_PAGES)
            ->withEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page->getId())
            ->getItem(
                $cacheKey,
                function () use ($pageKeyword) {
                    return $this->fetchPageWithFieldsFromDatabase($pageKeyword);
                }
            );
    }

    private function fetchPageWithFieldsFromDatabase(string $pageKeyword): array
    {

        $page = $this->pageRepository->findOneBy(['keyword' => $pageKeyword]);

        if (!$page) {
            $this->throwNotFound('Page not found');
        }

        // Check if user has access to the page
        $this->userContextAwareService->checkAccess($pageKeyword, 'select');

        // Get page type fields based on the page's type
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('ptf', 'f', 'ft')
            ->from(PageTypeField::class, 'ptf')
            ->innerJoin('ptf.field', 'f')
            ->innerJoin('f.type', 'ft')
            ->where('ptf.pageType = :pageTypeId')
            ->setParameter('pageTypeId', $page->getPageType()->getId())
            ->orderBy('f.name', 'ASC');

        $pageTypeFields = $qb->getQuery()->getResult();

        // Get page fields associated with this page
        $pageFieldsMap = [];
        $pageFields = $this->entityManager->getRepository(PageTypeField::class)->findBy(['pageType' => $page->getPageType()->getId()]);
        foreach ($pageFields as $pageField) {
            $pageFieldsMap[$pageField->getField()->getId()] = $pageField;
        }

        // Get all translations for this page's fields
        $translationsMap = [];
        $translations = $this->entityManager->getRepository(PagesFieldsTranslation::class)
            ->findBy(['page' => $page]);

        foreach ($translations as $translation) {
            $fieldId = $translation->getField()->getId();
            $langId = $translation->getLanguage()->getId();
            if (!isset($translationsMap[$fieldId])) {
                $translationsMap[$fieldId] = [];
            }
            $translationsMap[$fieldId][$langId] = $translation;
        }

        // Format fields with translations
        $formattedFields = [];
        foreach ($pageTypeFields as $pageTypeField) {
            $field = $pageTypeField->getField();
            $fieldId = $field->getId();

            // Get the pageField if it exists for this field
            $pageField = $pageFieldsMap[$fieldId] ?? null;

            $fieldData = [
                'id' => $fieldId,
                'name' => $field->getName(),
                'title' => $pageField ? $pageField->getTitle() : null,
                'type' => $field->getType() ? $field->getType()->getName() : null,
                'default_value' => $pageField ? $pageField->getDefaultValue() : null,
                'help' => $pageField ? $pageField->getHelp() : null,
                'display' => $field->isDisplay(),  // Whether it's a content field (1) or property field (0)
                'translations' => []
            ];

            // Handle translations based on display flag
            if ($field->isDisplay()) {
                // Content field (display=1) - can have translations for each language
                if (isset($translationsMap[$fieldId])) {
                    foreach ($translationsMap[$fieldId] as $translation) {
                        $language = $translation->getLanguage();
                        $fieldData['translations'][] = [
                            'language_id' => $language->getId(),
                            'language_code' => $language->getLocale(),
                            'content' => $translation->getContent()
                        ];
                    }
                }
            } else {
                // Property field (display=0) - use language_id = 1 only
                $propertyTranslation = $translationsMap[$fieldId][1] ?? null;
                if ($propertyTranslation) {
                    $fieldData['translations'][] = [
                        'language_id' => 1,
                        'language_code' => 'property',  // This is a property, not actually language-specific
                        'content' => $propertyTranslation->getContent()
                    ];
                }
            }

            $formattedFields[] = $fieldData;
        }

        // Return page data with fields and their translations
        return [
            'page' => [
                "id" => $page->getId(),
                "keyword" => $page->getKeyword(),
                "url" => $page->getUrl(),
                "parentPage" => null,
                "pageType" => [
                    "id" => $page->getPageType()->getId(),
                    "name" => $page->getPageType()->getName()
                ],
                "idType" => $page->getIdType(),
                "pageAccessType" => [
                    "id" => $page->getPageAccessType()->getId(),
                    "typeCode" => "pageAccessTypes",
                    "lookupCode" => $page->getPageAccessType()->getLookupCode(),
                    "lookupValue" => $page->getPageAccessType()->getLookupValue(),
                    "lookupDescription" => $page->getPageAccessType()->getLookupDescription()
                ],
                "headless" => $page->isHeadless(),
                "navPosition" => $page->getNavPosition(),
                "footerPosition" => $page->getFooterPosition(),
                "openAccess" => $page->isOpenAccess(),
                "system" => $page->isSystem()
            ],
            'fields' => $formattedFields
        ];
    }

    /**
     * Update page field translations
     * 
     * @param Page $page The page entity
     * @param array $fields The fields to update
     * @throws ServiceException If validation fails
     */
    public function updatePageFields(Page $page, array $fields): void
    {
        if (empty($fields)) {
            return;
        }

        // Validate that all fields belong to the page's page type
        $fieldIds = array_column($fields, 'fieldId');
        $pageType = $page->getPageType();
        if (!$pageType) {
            throw new ServiceException(
                sprintf("Page %s does not have a page type assigned", $page->getKeyword()),
                \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST
            );
        }

        $this->validatePageTypeFields($fieldIds, $pageType->getId(), $this->entityManager);

        // Update field translations using trait method
        $this->updatePageFieldTranslations($page->getId(), $fields, $this->entityManager);

        // Invalidate page cache after updates
        $this->cache
            ->withCategory(CacheService::CATEGORY_PAGES)
            ->invalidateEntityScope(CacheService::ENTITY_SCOPE_PAGE, $page->getId());
        $this->cache
            ->withCategory(CacheService::CATEGORY_PAGES)
            ->invalidateAllListsInCategory();
    }
}