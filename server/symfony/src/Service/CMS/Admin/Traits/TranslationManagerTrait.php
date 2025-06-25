<?php

namespace App\Service\CMS\Admin\Traits;

use App\Entity\PagesFieldsTranslation;
use App\Entity\SectionsFieldsTranslation;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Trait for managing field translations in admin services
 */
trait TranslationManagerTrait
{
    /**
     * Update page field translations
     * 
     * @param int $pageId The page ID
     * @param array $fields Array of field data
     * @param EntityManagerInterface $entityManager
     */
    protected function updatePageFieldTranslations(int $pageId, array $fields, EntityManagerInterface $entityManager): void
    {
        foreach ($fields as $field) {
            $fieldId = $field['fieldId'];
            $languageId = $field['languageId'];
            $content = $field['content'];

            // Check if translation exists
            $existingTranslation = $entityManager->getRepository(PagesFieldsTranslation::class)
                ->findOneBy([
                    'idPages' => $pageId,
                    'idFields' => $fieldId,
                    'idLanguages' => $languageId
                ]);

            if ($existingTranslation) {
                // Update existing translation
                $existingTranslation->setContent($content);
            } else {
                // Create new translation
                $this->createPageFieldTranslation($pageId, $fieldId, $languageId, $content, $entityManager);
            }
        }
    }

    /**
     * Update section field translations
     * 
     * @param int $sectionId The section ID
     * @param array $contentFields Content fields (display=1)
     * @param array $propertyFields Property fields (display=0)
     * @param EntityManagerInterface $entityManager
     */
    protected function updateSectionFieldTranslations(int $sectionId, array $contentFields, array $propertyFields, EntityManagerInterface $entityManager): void
    {
        // Update content field translations (display=1 fields)
        foreach ($contentFields as $field) {
            $this->updateSectionFieldTranslation($sectionId, $field['fieldId'], $field['languageId'], 1, $field['value'], $entityManager);
        }

        // Update property field translations (display=0 fields)
        foreach ($propertyFields as $field) {
            $content = is_bool($field['value']) ? ($field['value'] ? '1' : '0') : (string) $field['value'];
            $this->updateSectionFieldTranslation($sectionId, $field['fieldId'], 1, 1, $content, $entityManager);
        }
    }

    /**
     * Create a new page field translation
     */
    private function createPageFieldTranslation(int $pageId, int $fieldId, int $languageId, string $content, EntityManagerInterface $entityManager): void
    {
        $newTranslation = new PagesFieldsTranslation();
        $newTranslation->setIdPages($pageId);
        $newTranslation->setIdFields($fieldId);
        $newTranslation->setIdLanguages($languageId);
        $newTranslation->setContent($content);

        // Set entity relationships
        $page = $entityManager->getRepository(\App\Entity\Page::class)->find($pageId);
        if ($page) {
            $newTranslation->setPage($page);
        }

        $field = $entityManager->getRepository(\App\Entity\Field::class)->find($fieldId);
        if ($field) {
            $newTranslation->setField($field);
        }

        $language = $entityManager->getRepository(\App\Entity\Language::class)->find($languageId);
        if ($language) {
            $newTranslation->setLanguage($language);
        }

        $entityManager->persist($newTranslation);
    }

    /**
     * Update or create section field translation
     */
    private function updateSectionFieldTranslation(int $sectionId, int $fieldId, int $languageId, int $genderId, string $content, EntityManagerInterface $entityManager): void
    {
        // Check if translation exists
        $existingTranslation = $entityManager->getRepository(SectionsFieldsTranslation::class)
            ->findOneBy([
                'idSections' => $sectionId,
                'idFields' => $fieldId,
                'idLanguages' => $languageId,
                'idGenders' => $genderId
            ]);

        if ($existingTranslation) {
            // Update existing translation
            $existingTranslation->setContent($content);
        } else {
            // Create new translation
            $this->createSectionFieldTranslation($sectionId, $fieldId, $languageId, $genderId, $content, $entityManager);
        }
    }

    /**
     * Create a new section field translation
     */
    private function createSectionFieldTranslation(int $sectionId, int $fieldId, int $languageId, int $genderId, string $content, EntityManagerInterface $entityManager): void
    {
        $newTranslation = new SectionsFieldsTranslation();
        $newTranslation->setIdSections($sectionId);
        $newTranslation->setIdFields($fieldId);
        $newTranslation->setIdLanguages($languageId);
        $newTranslation->setIdGenders($genderId);
        $newTranslation->setContent($content);

        // Set entity relationships
        $section = $entityManager->getRepository(\App\Entity\Section::class)->find($sectionId);
        if ($section) {
            $newTranslation->setSection($section);
        }

        $field = $entityManager->getRepository(\App\Entity\Field::class)->find($fieldId);
        if ($field) {
            $newTranslation->setField($field);
        }

        $language = $entityManager->getRepository(\App\Entity\Language::class)->find($languageId);
        if ($language) {
            $newTranslation->setLanguage($language);
        }

        $gender = $entityManager->getRepository(\App\Entity\Gender::class)->find($genderId);
        if ($gender) {
            $newTranslation->setGender($gender);
        }

        $entityManager->persist($newTranslation);
    }
} 