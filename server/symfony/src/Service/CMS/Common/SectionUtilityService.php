<?php

namespace App\Service\CMS\Common;

use App\Repository\StylesFieldRepository;
use App\Service\CMS\DataService;

/**
 * Utility service for section-related operations
 * Provides common functionality used by both admin and frontend services
 */
class SectionUtilityService
{
    public function __construct(
        private readonly DataService $dataService,
        ?StylesFieldRepository $stylesFieldRepository = null
    ) {
        $this->stylesFieldRepository = $stylesFieldRepository;
    }

    /**
     * @var StylesFieldRepository|null
     */
    private ?StylesFieldRepository $stylesFieldRepository = null;
    
    /**
     * Set the StylesFieldRepository
     * 
     * @param StylesFieldRepository $stylesFieldRepository The repository to use for styles fields
     * @return self
     */
    public function setStylesFieldRepository(StylesFieldRepository $stylesFieldRepository): self
    {
        $this->stylesFieldRepository = $stylesFieldRepository;
        return $this;
    }
    
    /**
     * Build a nested hierarchical structure from flat sections array
     * 
     * @param array $sections Flat array of sections with path and level information
     * @return array Hierarchical structure of sections
     */
    public function buildNestedSections(array $sections): array
    {
        // Create a map of sections by ID for quick lookup
        $sectionsById = [];
        $rootSections = [];

        // First pass: index all sections by ID
        foreach ($sections as $section) {
            $section['children'] = [];
             $this->applySectionData($section);
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

        // Recursively sort children by position
        $sortChildren = function (&$nodes) use (&$sortChildren) {
            usort($nodes, function ($a, $b) {
                return ($a['position'] ?? 0) <=> ($b['position'] ?? 0);
            });
            foreach ($nodes as &$node) {
                if (!empty($node['children'])) {
                    $sortChildren($node['children']);
                }
            }
        };
        $sortChildren($rootSections);
        return $rootSections;
    }
    
    /**
     * Recursively extract all section IDs from a hierarchical sections structure
     * 
     * @param array $sections Hierarchical sections structure
     * @return array Flat array of section IDs
     */
    public function extractSectionIds(array $sections): array
    {
        $ids = [];
        
        foreach ($sections as $section) {
            if (isset($section['id'])) {
                $ids[] = $section['id'];
            }
            
            // Process children recursively
            if (!empty($section['children'])) {
                $childIds = $this->extractSectionIds($section['children']);
                $ids = array_merge($ids, $childIds);
            }
        }
        
        return $ids;
    }
    
    /**
     * Apply translations to sections recursively
     * 
     * @param array &$sections The sections to apply translations to (passed by reference)
     * @param array $translations The translations keyed by section ID
     * @param array $defaultTranslations Default language translations for fallback
     * @param array $propertyTranslations Property translations (language ID 1) for fields of type 1
     * @throws \LogicException If stylesFieldRepository is not set but style default values are needed
     */
    public function applySectionTranslations(
        array &$sections, 
        array $translations, 
        array $defaultTranslations = [], 
        array $propertyTranslations = []
    ): void {
        foreach ($sections as &$section) {
            $sectionId = $section['id'] ?? null;
            $fields = [];
            
            if ($sectionId) {
                // Get the section's style ID to fetch default values if needed
                $styleId = $section['id_styles'] ?? null;
                
                // First apply property translations (for fields of type 1)
                if (isset($propertyTranslations[$sectionId])) {
                    $section = array_merge($section, $propertyTranslations[$sectionId]);
                }
                
                // Then apply default language translations as fallback
                if (isset($defaultTranslations[$sectionId])) {
                    $section = array_merge($section, $defaultTranslations[$sectionId]);
                }
                
                // Finally apply requested language translations (overriding any fallbacks)
                if (isset($translations[$sectionId])) {
                    $section = array_merge($section, $translations[$sectionId]);
                }
                
                // For any fields that still don't have values, use default values from styles_fields table
                if ($styleId && $this->stylesFieldRepository !== null) {
                    // Get all fields for this section's style
                    $stylesFields = $this->stylesFieldRepository->findDefaultValuesByStyleId($styleId);
                    
                    // Apply default values for fields that don't have translations
                    foreach ($stylesFields as $fieldName => $defaultValue) {
                        // Only apply default value if the field doesn't already have a value
                        if (!isset($section[$fieldName]) || empty($section[$fieldName]['content'])) {
                            $fields[$fieldName] = [
                                'content' => $defaultValue,
                                'meta' => null
                            ];
                        }
                    }
                } elseif ($styleId && $this->stylesFieldRepository === null) {
                    throw new \LogicException('StylesFieldRepository is required for applying default style values');
                }
            }
            
            $section['fields'] = $fields;
            
            // Process children recursively
            if (isset($section['children']) && is_array($section['children'])) {
                $this->applySectionTranslations(
                    $section['children'], 
                    $translations, 
                    $defaultTranslations, 
                    $propertyTranslations
                );
            }
        }
    }
    
    /**
     * Normalize a Section entity for API response
     * 
     * @param object $section Section entity or array with section data
     * @return array Normalized section data
     */
    public function normalizeSection($section): array
    {
        if (is_object($section) && method_exists($section, 'getId')) {
            // It's an entity, convert to array
            return [
                'id' => $section->getId(),
                'name' => $section->getName(),
                'id_styles' => $section->getStyle() ? $section->getStyle()->getId() : null,
                'style_name' => $section->getStyle() ? $section->getStyle()->getName() : null,
            ];
        } else if (is_array($section)) {
            // It's already an array, ensure it has the expected structure
            return array_merge([
                'id' => $section['id'] ?? null,
                'name' => $section['name'] ?? null,
                'id_styles' => $section['id_styles'] ?? null,
                'style_name' => $section['style_name'] ?? null,
            ], $section);
        }
        
        // Fallback for unexpected input
        return [];
    }

    /**
     * Apply data to sections
     * 
     * @param array &$sections The sections to apply data to (passed by reference)
     */
    public function applySectionsData(array &$sections): void
    {
        foreach ($sections as $section) {
            $this->applySectionData($section);
        }
    }

    /**
     * Apply data to a section
     * 
     * @param array &$section The section to apply data to (passed by reference)
     */
    public function applySectionData(array &$section): void
    {        
         $section['section_data'] = [];
         if($section['style_name'] == 'formUserInputRecord') {
            $section['section_data'] = $this->dataService->getFormUserInputRecordData($section['id']);
         }
    }
}
