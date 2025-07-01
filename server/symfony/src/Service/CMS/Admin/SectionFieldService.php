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

        // Get all StylesField for this style
        $stylesFields = $style->getStylesFields();
        
        // Fetch all field translations for this section
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t, l, f, g, ft')
            ->from('App\Entity\SectionsFieldsTranslation', 't')
            ->leftJoin('t.language', 'l')
            ->leftJoin('t.field', 'f')
            ->leftJoin('f.type', 'ft')
            ->leftJoin('t.gender', 'g')
            ->where('t.section = :section')
            ->setParameter('section', $section);
        $translations = $qb->getQuery()->getResult();

        // Group translations by field and language
        $translationsByFieldLang = [];
        foreach ($translations as $tr) {
            $fieldId = $tr->getField()->getId();
            $langId = $tr->getLanguage()->getId();
            $genderId = $tr->getGender()->getId();
            if (!isset($translationsByFieldLang[$fieldId])) {
                $translationsByFieldLang[$fieldId] = [];
            }
            if (!isset($translationsByFieldLang[$fieldId][$langId])) {
                $translationsByFieldLang[$fieldId][$langId] = [];
            }
            $translationsByFieldLang[$fieldId][$langId][$genderId] = [
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
                'type' => $field->getType() ? $field->getType()->getName() : null,
                'default_value' => $stylesField->getDefaultValue(),
                'help' => $stylesField->getHelp(),
                'disabled' => $stylesField->isDisabled(),
                'hidden' => $stylesField->getHidden(),
                'display' => $field->isDisplay(),
                'translations' => []
            ];
            
            // Handle translations based on display flag
            if ($field->isDisplay()) {
                // Content field (display=1) - can have translations for each language
                if (isset($translationsByFieldLang[$fieldId])) {
                    foreach ($translationsByFieldLang[$fieldId] as $langId => $genderTranslations) {
                        foreach ($genderTranslations as $genderId => $translation) {
                            $fieldData['translations'][] = [
                                'language_id' => $langId,
                                'gender_id' => $genderId,
                                'content' => $translation['content'],
                                'meta' => $translation['meta']
                            ];
                        }
                    }
                }
            } else {
                // Property field (display=0) - use language_id = 1 only
                if (isset($translationsByFieldLang[$fieldId][1])) {
                    $propertyTranslation = $translationsByFieldLang[$fieldId][1][1] ?? null;
                    if ($propertyTranslation) {
                        $fieldData['translations'][] = [
                            'language_id' => 1,
                            'language_code' => 'property',  // This is a property, not actually language-specific
                            'gender_id' => 1,
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