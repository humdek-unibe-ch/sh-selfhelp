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
        if ($fieldType === 'select-css') {
            // format ["value" => "class_name", "text" => "class_name"]
            $options = $this->getCss();
        }
        if ($fieldType === 'select-page-keyword') {
            // format ["value" => "page_id", "text" => "page_keyword"]
            $options = $this->getPageKeywords();
        }
        return in_array($fieldType, ['select-group', 'select-data_table', 'select-css', 'select-page-keyword']) ? [
            'multiSelect' => in_array($fieldType, ['select-group', 'select-css']),
            'creatable' => in_array($fieldType, ['select-css']),
            'options' => $options
        ] : [];
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
     * Get CSS classes for select-css field type
     * Loads from frontend-generated JSON file containing ALL_CSS_CLASSES
     * 
     * @return array The CSS classes formatted as options
     */
    private function getCss(): array
    {
        static $cssClasses = null;
        
        if ($cssClasses === null) {
            // Try to load from frontend-generated JSON file
            $jsonPath = $this->getProjectDir() . '/public/assets/tailwind-classes.json';
            
            if (file_exists($jsonPath)) {
                $jsonContent = file_get_contents($jsonPath);
                $allClasses = json_decode($jsonContent, true);
                
                if (is_array($allClasses)) {
                    // Format for select: ["value" => "class_name", "text" => "class_name"]
                    $cssClasses = array_map(fn($class) => [
                        'value' => $class,
                        'text' => $class
                    ], $allClasses);
                } else {
                    $cssClasses = $this->getFallbackCssClasses();
                }
            } else {
                // Fallback to a curated list of common classes
                $cssClasses = $this->getFallbackCssClasses();
            }
        }
        
        return $cssClasses;
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
     * Get fallback CSS classes when JSON file is not available
     * 
     * @return array Common CSS classes formatted as options
     */
    private function getFallbackCssClasses(): array
    {
        $commonClasses = [
            // Layout
            'container', 'mx-auto', 'flex', 'grid', 'block', 'inline-block', 'hidden',
            
            // Spacing
            'p-0', 'p-1', 'p-2', 'p-3', 'p-4', 'p-6', 'p-8',
            'px-2', 'px-4', 'px-6', 'py-2', 'py-4', 'py-6',
            'm-0', 'm-1', 'm-2', 'm-3', 'm-4', 'm-6', 'm-8',
            'mx-auto', 'mx-2', 'mx-4', 'my-2', 'my-4',
            
            // Typography
            'text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl',
            'font-normal', 'font-medium', 'font-semibold', 'font-bold',
            'text-left', 'text-center', 'text-right',
            
            // Colors
            'text-white', 'text-black', 'text-gray-500', 'text-gray-700', 'text-gray-900',
            'bg-white', 'bg-black', 'bg-gray-50', 'bg-gray-100', 'bg-gray-500',
            'bg-blue-500', 'bg-green-500', 'bg-red-500',
            
            // Borders & Radius
            'border', 'border-2', 'border-gray-300', 'rounded', 'rounded-lg',
            
            // Sizing
            'w-full', 'w-1/2', 'w-1/3', 'w-2/3', 'h-auto', 'h-full',
            
            // Flexbox
            'justify-center', 'justify-between', 'items-center', 'items-start',
            
            // Grid
            'grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4',
            'gap-2', 'gap-4', 'gap-6',
            
            // Responsive
            'sm:block', 'md:flex', 'lg:grid-cols-3', 'xl:text-xl',
            
            // States
            'hover:bg-gray-100', 'focus:outline-none', 'active:bg-gray-200'
        ];
        
        return array_map(fn($class) => [
            'value' => $class,
            'text' => $class
        ], $commonClasses);
    }

    /**
     * Get project directory path
     * 
     * @return string
     */
    private function getProjectDir(): string
    {
        return dirname(__DIR__, 4); // Navigate up from src/Service/CMS/Admin to project root
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
