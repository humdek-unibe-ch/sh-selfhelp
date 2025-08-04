<?php

namespace App\Service\CMS\Admin;

use App\Entity\Section;
use App\Exception\ServiceException;
use App\Service\CMS\Admin\Traits\TranslationManagerTrait;
use App\Service\CMS\Admin\Traits\FieldValidatorTrait;
use App\Service\Core\UserContextAwareService;
use App\Service\ACL\ACLService;
use App\Service\Auth\UserContextService;
use App\Repository\PageRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service for handling section field operations
 */
class SectionFieldService extends UserContextAwareService
{
    use TranslationManagerTrait;
    use FieldValidatorTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        ACLService $aclService,
        UserContextService $userContextService,
        PageRepository $pageRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct($userContextService, $aclService, $pageRepository, $sectionRepository);
    }

    /**
     * Get section fields with translations
     * 
     * @param Section $section The section entity
     * @return array The formatted fields with translations
     */
    public function getSectionFields(Section $section): array
    {
        // Get style and its fields
        $style = $section->getStyle();
        if (!$style) {
            return [];
        }

        // Get all StylesField for this style ordered by priority asc and field name asc
        $stylesFields = $style->getStylesFields()->toArray();
        usort($stylesFields, function ($a, $b) {
            $priorityA = $a->getField()->getType()->getPosition() ?? PHP_INT_MAX;
            $priorityB = $b->getField()->getType()->getPosition() ?? PHP_INT_MAX;
            if ($priorityA !== $priorityB) {
                return $priorityA - $priorityB;
            }
            return strcasecmp($a->getField()->getName(), $b->getField()->getName());
        });

        // Fetch all field translations for this section
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t, l, f, ft')
            ->from('App\Entity\SectionsFieldsTranslation', 't')
            ->leftJoin('t.language', 'l')
            ->leftJoin('t.field', 'f')
            ->leftJoin('f.type', 'ft')
            ->where('t.section = :section')
            ->setParameter('section', $section);
        $translations = $qb->getQuery()->getResult();

        // Group translations by field and language
        $translationsByFieldLang = [];
        foreach ($translations as $tr) {
            $fieldId = $tr->getField()->getId();
            $langId = $tr->getLanguage()->getId();
            if (!isset($translationsByFieldLang[$fieldId])) {
                $translationsByFieldLang[$fieldId] = [];
            }
            if (!isset($translationsByFieldLang[$fieldId][$langId])) {
                $translationsByFieldLang[$fieldId][$langId] = [];
            }
            $translationsByFieldLang[$fieldId][$langId] = [
                'content' => $tr->getContent(),
                'meta' => $tr->getMeta(),
            ];
        }

        // Format fields with translations
        $formattedFields = [];
        foreach ($stylesFields as $stylesField) {
            $field = $stylesField->getField();
            if (!$field) continue;

            $fieldId = $field->getId();

            $fieldData = [
                'id' => $fieldId,
                'name' => $field->getName(),
                'title' => $stylesField->getTitle(),
                'type' => $field->getType() ? $field->getType()->getName() : null,
                'default_value' => $stylesField->getDefaultValue(),
                'help' => $stylesField->getHelp(),
                'disabled' => $stylesField->isDisabled(),
                'hidden' => $stylesField->getHidden(),
                'display' => $field->isDisplay(),
                'translations' => [],
                'fieldConfig' => $this->getFieldConfig($field->getType() ? $field->getType()->getName() : []),
            ];

            // Handle translations based on display flag
            if ($field->isDisplay()) {
                // Content field (display=1) - can have translations for each language
                if (isset($translationsByFieldLang[$fieldId])) {
                    foreach ($translationsByFieldLang[$fieldId] as $langId => $translation) {
                        $fieldData['translations'][] = [
                            'language_id' => $langId,
                            'content' => $translation['content'],
                            'meta' => $translation['meta']
                        ];
                    }
                }
            } else {
                // Property field (display=0) - use language_id = 1 only
                if (isset($translationsByFieldLang[$fieldId][1])) {
                    $propertyTranslation = $translationsByFieldLang[$fieldId][1] ?? null;
                    if ($propertyTranslation) {
                        $fieldData['translations'][] = [
                            'language_id' => 1,
                            'language_code' => 'property',  // This is a property, not actually language-specific
                            'content' => $propertyTranslation['content'],
                            'meta' => $propertyTranslation['meta']
                        ];
                    }
                }
            }

            $formattedFields[] = $fieldData;
        }

        return $formattedFields;
    }

    /**
     * Get field configuration based on field type
     * 
     * @param string $fieldType The field type
     * @return array The field configuration
     */
    private function getFieldConfig($fieldType): array
    {
        $options = [];
        if ($fieldType === 'select-group') {
            // format ["value" => "group_id", "text" => "group_name"]
            $options = $this->getGroups();
        }
        if ($fieldType === 'select-data_table') {
            // format ["value" => "data_table_id", "text" => "data_table_name"]
            $options = $this->getDataTables();
        }
        if ($fieldType === 'select-page-keyword') {
            // format ["value" => "page_id", "text" => "page_keyword"]
            $options = $this->getPageKeywords();
        }
        $config = [];
        
        if (in_array($fieldType, ['select-group', 'select-data_table', 'select-css', 'select-page-keyword'])) {
            $config = [
                'multiSelect' => in_array($fieldType, ['select-group', 'select-css']),
                'creatable' => in_array($fieldType, ['select-css']),
                'options' => $options
            ];
            
            // Add API URL for CSS classes to allow frontend to fetch on demand
            if ($fieldType === 'select-css') {
                $config['apiUrl'] = '/cms-api/v1/frontend/css-classes';
            }
        }
        
        return $config;
    }

    /**
     * Get groups for select-group field type
     * 
     * @return array The groups formatted as options
     */
    private function getGroups(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('g.id, g.name')
            ->from('App\Entity\Group', 'g')
            ->orderBy('g.name', 'ASC');
        
        $groups = $qb->getQuery()->getResult();
        
        return array_map(fn($group) => [
            'value' => (string)$group['id'],
            'text' => $group['name']
        ], $groups);
    }

    /**
     * Get data tables for select-data_table field type
     * 
     * @return array The data tables formatted as options
     */
    private function getDataTables(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('dt.id, dt.name')
            ->from('App\Entity\DataTable', 'dt')
            ->orderBy('dt.name', 'ASC');
        
        $dataTables = $qb->getQuery()->getResult();
        
        return array_map(fn($table) => [
            'value' => (string)$table['id'],
            'text' => $table['name']
        ], $dataTables);
    }

    /**
     * Get page keywords for select-page-keyword field type
     * 
     * @return array The page keywords formatted as options
     */
    private function getPageKeywords(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.id, p.keyword')
            ->from('App\Entity\Page', 'p')
            ->where('p.keyword IS NOT NULL')
            ->orderBy('p.keyword', 'ASC');
        
        $pages = $qb->getQuery()->getResult();
        
        return array_map(fn($page) => [
            'value' => (string)$page['id'],
            'text' => $page['keyword']
        ], $pages);
    }

    /**
     * Update section field translations
     * 
     * @param Section $section The section entity
     * @param array $contentFields Content fields (display=1)
     * @param array $propertyFields Property fields (display=0)
     * @throws ServiceException If validation fails
     */
    public function updateSectionFields(Section $section, array $contentFields, array $propertyFields): void
    {
        // Validate that all fields belong to the section's style
        $allFieldIds = array_merge(
            array_column($contentFields, 'fieldId'),
            array_column($propertyFields, 'fieldId')
        );

        if (!empty($allFieldIds)) {
            $this->validateStyleFields($allFieldIds, $section->getStyle()->getId(), $this->entityManager);
        }

        // Update field translations using trait method
        $this->updateSectionFieldTranslations($section->getId(), $contentFields, $propertyFields, $this->entityManager);
    }
}
