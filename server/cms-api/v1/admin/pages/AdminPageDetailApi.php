<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../../BaseApiRequest.php";

/**
 * @class AdminPageDetailApi
 * @brief API handler for detailed page information and structure
 * @extends BaseApiRequest
 * 
 * This class provides endpoints for retrieving detailed information about pages,
 * including their sections, fields, and hierarchical structure. It's designed to
 * provide the data needed for the React frontend to render CMS content.
 * 
 * Security Features:
 * - Authentication check on all endpoints
 * - ACL (Access Control List) validation
 * - Input validation and sanitization
 */
class AdminPageDetailApi extends BaseApiRequest
{
    /**
     * @brief Constructor for AdminPageDetailApi
     * 
     * @param object $services The service handler instance which holds all services
     * @param string $keyword The keyword identifier for the page
     */
    public function __construct($services, $keyword)
    {
        parent::__construct(services: $services, keyword: $keyword);
        $this->response = new CmsApiResponse();
    }

    /** Private methods */
    /**
     * @brief Transforms a flat array of sections into a nested hierarchical structure
     * 
     * @param array $sections Flat array of section objects with level and path properties
     * @return array Nested array with children properly nested under their parents
     */
    private function buildNestedSections(array $sections): array
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
        
        return $rootSections;
    }

    /**
     * @brief Retrieves sections and structure for a specific page by keyword
     * 
     * @param string $page_keyword The keyword of the page to retrieve sections for
     * @return void Response is handled through CmsApiResponse
     * 
     * @details
     * This method:
     * 1. Verifies the page exists and user has access to it
     * 2. Retrieves the page's section hierarchy
     * 3. Retrieves all section data including fields
     * 4. Returns a structured JSON response with all page content
     * 
     * @throws Exception If page not found or user doesn't have access
     */
    public function GET_page_fields($page_keyword): array
    {
        // Validate page exists and user has access
        $page_id = $this->db->fetch_page_id_by_keyword($page_keyword);

        if (!$page_id) {
            $this->error_response(error: "Page not found", status: 404);
            return [];
        }

        // Check user has access to the page
        if (!$this->acl->has_access($this->get_user_id(), $page_id, 'select')) {
            $this->error_response(error: "Access denied", status: 403);
            return [];
        }

        try {
            $sql = "SELECT pft.id_fields, f.name as field_name, pft.id_languages, pft.content
                FROM pages_fields_translation pft
                JOIN fields f ON pft.id_fields = f.id
                WHERE pft.id_pages = :page_id";

            $fields = $this->db->query_db($sql, [':page_id' => $page_id]);

            // Organize fields by name and language
            $page_fields = [];
            foreach ($fields as $field) {
                if (!isset($page_fields[$field['field_name']])) {
                    $page_fields[$field['field_name']] = [];
                }

                $page_fields[$field['field_name']][$field['id_languages']] = [
                    'id' => $field['id_fields'],
                    'content' => $field['content']
                ];
            }

            // Combine everything into a complete page structure
            $result = [
                'page' => array(
                    'fields' => $page_fields,
                    'page_id' => $page_id,
                    'page_keyword' => $page_keyword
                )
            ];

            return $result;
        } catch (Exception $e) {
            throw new Exception("Error retrieving page sections: " . $e->getMessage());
        }
    }

    public function GET_page_sections($page_keyword): array
    {
        $page_id = $this->db->fetch_page_id_by_keyword($page_keyword);
        $sql = "CALL get_page_sections_hierarchical(:page_id);";
        $sections = $this->db->query_db($sql, [':page_id' => $page_id]);        
        return $this->buildNestedSections($sections);
    }
}
?>
