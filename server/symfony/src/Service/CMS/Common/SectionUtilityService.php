<?php

namespace App\Service\CMS\Common;

/**
 * Utility service for section-related operations
 * Provides common functionality used by both admin and frontend services
 */
class SectionUtilityService
{
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
}
