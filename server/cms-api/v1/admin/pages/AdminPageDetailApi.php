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

    /**
     * @brief Get basic page information
     * 
     * @param int $page_id The ID of the page
     * @return array Basic page information
     */
    private function get_page_info(int $page_id): array
    {
        $sql = "SELECT id, keyword, url, protocol, id_actions, parent, is_headless, 
                      nav_position, footer_position, id_type, id_pageAccessTypes, is_open_access
                FROM pages 
                WHERE id = :page_id";

        $page = $this->db->query_db_first($sql, [':page_id' => $page_id]);

        if (!$page) {
            throw new Exception("Page not found");
        }

        return $page;
    }

    /**
     * @brief Get section hierarchy for a page
     * 
     * @param int $page_id The ID of the page
     * @return array Hierarchical structure of sections
     */
    private function get_page_sections_hierarchy(int $page_id): array
    {
        // Get the root sections for this page
        $sql = "SELECT s.id, s.name as section_name, s.id_styles, st.name as style_name 
                FROM sections s
                JOIN pages_sections ps ON s.id = ps.id_sections
                JOIN styles st ON s.id_styles = st.id
                WHERE ps.id_pages = :page_id AND ps.relation = 'page'
                ORDER BY ps.position";

        $root_sections = $this->db->query_db($sql, [':page_id' => $page_id]);

        // Build full section hierarchy
        $sections = [];
        foreach ($root_sections as $section) {
            $section_data = [
                'id' => $section['id'],
                'name' => $section['section_name'],
                'style' => [
                    'id' => $section['id_styles'],
                    'name' => $section['style_name']
                ],
                'fields' => $this->get_section_fields($section['id']),
                'children' => $this->get_section_children($section['id'])
            ];

            $sections[] = $section_data;
        }

        return $sections;
    }

    /**
     * @brief Get all fields for a section with translations
     * 
     * @param int $section_id The ID of the section
     * @return array Section fields with translations
     */
    private function get_section_fields(int $section_id): array
    {
        $sql = "SELECT sft.id_fields, f.name as field_name, sft.id_languages, 
                       sft.id_genders, sft.content, sft.meta
                FROM sections_fields_translation sft
                JOIN fields f ON sft.id_fields = f.id
                WHERE sft.id_sections = :section_id";

        $fields = $this->db->query_db($sql, [':section_id' => $section_id]);

        // Organize fields by name, language and gender
        $organized_fields = [];
        foreach ($fields as $field) {
            $field_name = $field['field_name'];
            $language_id = $field['id_languages'];
            $gender_id = $field['id_genders'];

            if (!isset($organized_fields[$field_name])) {
                $organized_fields[$field_name] = [];
            }

            if (!isset($organized_fields[$field_name][$language_id])) {
                $organized_fields[$field_name][$language_id] = [];
            }

            $field_data = [
                'id' => $field['id_fields'],
                'content' => $field['content']
            ];

            if ($field['meta']) {
                $field_data['meta'] = json_decode($field['meta'], true);
            }

            $organized_fields[$field_name][$language_id][$gender_id] = $field_data;
        }

        return $organized_fields;
    }

    /**
     * @brief Get child sections recursively
     * 
     * @param int $parent_section_id The ID of the parent section
     * @return array Child sections with their data and children
     */
    private function get_section_children(int $parent_section_id): array
    {
        $sql = "SELECT s.id, s.name as section_name, s.id_styles, st.name as style_name 
                FROM sections s
                JOIN sections_sections ss ON s.id = ss.id_sections
                JOIN styles st ON s.id_styles = st.id
                WHERE ss.id_sections_parent = :parent_id
                ORDER BY ss.position";

        $children = $this->db->query_db($sql, [':parent_id' => $parent_section_id]);

        $child_sections = [];
        foreach ($children as $child) {
            $child_data = [
                'id' => $child['id'],
                'name' => $child['section_name'],
                'style' => [
                    'id' => $child['id_styles'],
                    'name' => $child['style_name']
                ],
                'fields' => $this->get_section_fields($child['id']),
                'children' => $this->get_section_children($child['id'])
            ];

            $child_sections[] = $child_data;
        }

        return $child_sections;
    }
}
?>
