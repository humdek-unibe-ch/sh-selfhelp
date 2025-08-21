<?php

namespace App\Service\CMS\Admin\Traits;

use App\Entity\Field;
use App\Entity\Language;
use App\Entity\Page;
use App\Entity\PagesFieldsTranslation;
use App\Entity\Section;
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
        foreach ($fields as $fieldData) {
            $fieldId = $fieldData['fieldId'];
            $languageId = $fieldData['languageId'];
            $content = $fieldData['content'];

            // Check if translation exists
            $page = $entityManager->getRepository(Page::class)->find($pageId);
            $field = $entityManager->getRepository(Field::class)->find($fieldId);
            $language = $entityManager->getRepository(Language::class)->find($languageId);
            
            $existingTranslation = $entityManager->getRepository(PagesFieldsTranslation::class)
                ->findOneBy([
                    'page' => $page,
                    'field' => $field,
                    'language' => $language
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
        foreach ($contentFields as $fieldData) {
            $this->updateSectionFieldTranslation($sectionId, $fieldData['fieldId'], $fieldData['languageId'], $fieldData['value'], $entityManager);
        }

        // Update property field translations (display=0 fields)
        foreach ($propertyFields as $fieldData) {
            $content = is_bool($fieldData['value']) ? ($fieldData['value'] ? '1' : '0') : (string) $fieldData['value'];
            $this->updateSectionFieldTranslation($sectionId, $fieldData['fieldId'], 1, $content, $entityManager);
        }
    }

    /**
     * Create a new page field translation
     */
    private function createPageFieldTranslation(int $pageId, int $fieldId, int $languageId, string $content, EntityManagerInterface $entityManager): void
    {
        $newTranslation = new PagesFieldsTranslation();
        $newTranslation->setContent($content);

        // Set entity relationships
        $page = $entityManager->getRepository(Page::class)->find($pageId);
        if ($page) {
            $newTranslation->setPage($page);
        }

        $field = $entityManager->getRepository(Field::class)->find($fieldId);
        if ($field) {
            $newTranslation->setField($field);
        }

        $language = $entityManager->getRepository(Language::class)->find($languageId);
        if ($language) {
            $newTranslation->setLanguage($language);
        }

        $entityManager->persist($newTranslation);
    }

    /**
     * Update or create section field translation
     */
    private function updateSectionFieldTranslation(int $sectionId, int $fieldId, int $languageId, string $content, EntityManagerInterface $entityManager): void
    {
        // Check if translation exists
        $section = $entityManager->getRepository(Section::class)->find($sectionId);
        $field = $entityManager->getRepository(Field::class)->find($fieldId);
        $language = $entityManager->getRepository(Language::class)->find($languageId);
        
        $existingTranslation = $entityManager->getRepository(SectionsFieldsTranslation::class)
            ->findOneBy([
                'section' => $section,
                'field' => $field,
                'language' => $language
            ]);

        if ($existingTranslation) {
            // Update existing translation
            $existingTranslation->setContent($content);
        } else {
            // Create new translation
            $this->createSectionFieldTranslation($sectionId, $fieldId, $languageId, $content, $entityManager);
        }
    }

    /**
     * Create a new section field translation
     */
    private function createSectionFieldTranslation(int $sectionId, int $fieldId, int $languageId, string $content, EntityManagerInterface $entityManager): void
    {
        $newTranslation = new SectionsFieldsTranslation();
        $newTranslation->setContent($content);

        // Set entity relationships
        $section = $entityManager->getRepository(Section::class)->find($sectionId);
        if ($section) {
            $newTranslation->setSection($section);
        }

        $field = $entityManager->getRepository(Field::class)->find($fieldId);
        if ($field) {
            $newTranslation->setField($field);
        }

        $language = $entityManager->getRepository(Language::class)->find($languageId);
        if ($language) {
            $newTranslation->setLanguage($language);
        }

        $entityManager->persist($newTranslation);
    }
} 