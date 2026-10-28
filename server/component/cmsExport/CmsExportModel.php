<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

use Swaggest\JsonSchema\Schema;

require_once __DIR__ . "/../BaseModel.php";
require_once __DIR__ . "/../../service/ext/swaggest_json_schema_0.12.39.0_require/vendor/autoload.php";

/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class CmsExportModel extends BaseModel
{

    /* Public Properties *****************************************************/

    /**
     * Page or section
     */
    public $type;

    /** 
     * Id of the page or section
     */
    public $id;

    /**
     * The exported json
     */
    public $json;

    /* Private Properties *****************************************************/

    /**
     * The ids of the sections which are being exported at the moment (the
     * section and all its parents). A section which is exported inside itself
     * is a circular reference.
     */
    private $path = array();

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $type, $id)
    {
        parent::__construct($services);
        $this->type = $type;
        $this->id = $id;
    }

    /* Private Methods ********************************************************/

    /**
     * Fetch the section children for a section
     * @param int $parent_id the parent section_id
     * @retval array
     * Array with the children for the section
     */
    private function fetch_section_children($parent_id)
    {
        $this->enter_section($parent_id);
        $sql = "SELECT parent, child, sh.position, parent.name as psarent_section_name, children.section_name as children_section_name,
                content, meta, style_name, field_name, locale, gender, children.id_styles, children.id_fields
                FROM sections_hierarchy sh
                INNER JOIN sections parent ON (sh.parent = parent.id)
                INNER JOIN view_sections_fields children ON (sh.child = children.id_sections)
                WHERE sh.parent = :id_sections
                ORDER BY parent, position, child";
        $section_sql = $this->db->query_db($sql, array(":id_sections" => $parent_id));
        $children = array();
        $child = array();
        foreach ($section_sql as $row => $field) {
            if (!isset($child['section_name']) || $child['section_name'] != $field['children_section_name']) {
                if (isset($child['section_name'])) {
                    $children[] = $child;
                }
                // new child
                $child = array();
                $child['section_name'] = $field['children_section_name'];
                $child['id_sections'] = intval($field['child']);
                $child['style_name'] = $field['style_name'];
                $child['id_styles'] = intval($field['id_styles']);
                $child['position'] = intval($field['position']);
                $child['fields'] = array(); //initalize empty array for the section fields
                $child['meta'] = $field['meta'];
                $child['children'] = $this->fetch_section_children($field['child']);
            }
            $child['fields'][] = array(
                "id_styles" => intval($field['id_styles']),
                "style_name" => $field['style_name'],
                "field_name" => $field['field_name'],
                "id_fields" => intval($field['id_fields']),
                "locale" => $field['locale'],
                "gender" => $field['gender'],
                "content" => $field['content'],
                "meta" => $field['meta'],
            );
        }
        if (count($child) > 0) {
            // add the child only if exists
            $children[] = $child;
        }
        unset($this->path[intval($parent_id)]);
        return $children;
    }

    /**
     * Mark a section as being exported. A section can be used in several
     * places (it is exported in each of them) but it can never contain itself.
     * @param int $id the id of the section
     * @throws Exception if the section is already being exported
     */
    private function enter_section($id)
    {
        $id = intval($id);
        if (isset($this->path[$id])) {
            throw new Exception("The section with the id " . $id . " contains itself (circular reference). It cannot be exported.");
        }
        $this->path[$id] = true;
    }

    /**
     * Checks whether the user may export a section. The access rights of
     * sections are inherited from pages: the user needs select access to at
     * least one page which uses the section. A section which is not assigned
     * to a page has no page to inherit from, the access to the export is
     * granted by the route.
     * @param int $id the id of the section
     * @retval bool true if the section can be exported
     */
    private function can_export_section($id)
    {
        $lineage = $this->db->fetch_section_lineage($id);
        if ($lineage === false) {
            return false;
        }
        if (count($lineage['pages']) === 0) {
            return true;
        }
        foreach ($lineage['pages'] as $id_page) {
            if ($this->acl->has_access_select($_SESSION['id_user'], $id_page)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fetch the section that we want to export
     * @param int $id section_id that we want to export
     * @retval array
     * Array with the section information
     */
    private function fetch_section($id)
    {
        $sql = "SELECT *
                FROM view_sections_fields
                WHERE id_sections = :id_sections";
        $section_sql = $this->db->query_db($sql, array(":id_sections" => $id));
        $section = array();
        $json = array();
        foreach ($section_sql as $row => $field) {
            if (!isset($section['section_name'])) {
                // the section is not yet defined
                $section['section_name'] = $field['section_name'];
                $section['id_sections'] = intval($field['id_sections']);
                $section['style_name'] = $field['style_name'];
                $section['id_styles'] = intval($field['id_styles']);
                $section['position'] = 0;
                $section['fields'] = array(); //initalize empty array for the section fields
                $section['children'] = $this->fetch_section_children($field['id_sections']);
                $section['meta'] = $field['meta'];
                $json['section'] = $section;                
            }
            $json['section']['fields'][] = array(
                "id_styles" => intval($field['id_styles']),
                "style_name" => $field['style_name'],
                "field_name" => $field['field_name'],
                "id_fields" => intval($field['id_fields']),
                "locale" => $field['locale'],
                "gender" => $field['gender'],
                "content" => $field['content'],
                "meta" => $field['meta'],
            );
        }
        return $json;
    }

    /**
     * Add the navigation items (`sections_navigation`) to a section and to all
     * its descendants. Navigation items are linked to a page and not to the
     * section hierarchy, so they are not part of a plain section export.
     * @param array $section
     * the exported section, it is extended with the key `nav_children`
     */
    private function attach_nav_children(&$section)
    {
        $this->enter_section($section['id_sections']);
        $items = $this->db->query_db(
            "SELECT child, position FROM sections_navigation WHERE parent = :id ORDER BY position, child",
            array(":id" => $section['id_sections'])
        );
        foreach ($items as $item) {
            $nav_json = $this->fetch_section(intval($item['child']));
            if (!isset($nav_json['section'])) {
                continue;
            }
            $nav_section = $nav_json['section'];
            $nav_section['position'] = intval($item['position']);
            $this->attach_nav_children($nav_section);
            $section['nav_children'][] = $nav_section;
        }
        foreach ($section['children'] as $idx => $child) {
            $this->attach_nav_children($section['children'][$idx]);
        }
        unset($this->path[intval($section['id_sections'])]);
    }

    /**
     * Fetch the page that we want to export. The page contains its own
     * properties, the page fields (title, description, ...) and all sections
     * (including their children) which are assigned to the page. Navigation
     * pages also contain the navigation container and its items.
     * @param int $id page id that we want to export
     * @retval array
     * Array with the page information or false if the page does not exist
     */
    private function fetch_page($id)
    {
        $page = $this->db->query_db_first(
            "SELECT p.id, p.keyword, p.url, p.protocol, p.is_headless, p.id_navigation_section,
                    a.name AS action, pt.name AS page_type, l.lookup_code AS page_access_type
            FROM pages AS p
            LEFT JOIN actions AS a ON a.id = p.id_actions
            LEFT JOIN pageType AS pt ON pt.id = p.id_type
            LEFT JOIN lookups AS l ON l.id = p.id_pageAccessTypes
            WHERE p.id = :id",
            array(":id" => $id)
        );
        if (!$page) {
            return false;
        }
        $json = array();
        $json['page'] = array(
            'keyword' => $page['keyword'],
            'url' => (string)$page['url'],
            'protocol' => (string)$page['protocol'],
            'action' => (string)$page['action'],
            'page_type' => (string)$page['page_type'],
            'page_access_type' => (string)$page['page_access_type'],
            'is_headless' => (bool)intval($page['is_headless']),
            'fields' => array(),
            'sections' => array()
        );
        $fields = $this->db->query_db(
            "SELECT f.name AS field_name, l.locale, pft.content
            FROM pages_fields_translation AS pft
            INNER JOIN fields AS f ON f.id = pft.id_fields
            INNER JOIN languages AS l ON l.id = pft.id_languages
            WHERE pft.id_pages = :id
            ORDER BY f.name, l.id",
            array(":id" => $id)
        );
        foreach ($fields as $field) {
            $json['page']['fields'][] = array(
                "field_name" => $field['field_name'],
                "locale" => $field['locale'],
                "content" => (string)$field['content']
            );
        }
        $sections = $this->db->query_db(
            "SELECT id_sections, position FROM pages_sections WHERE id_pages = :id ORDER BY position, id_sections",
            array(":id" => $id)
        );
        foreach ($sections as $row) {
            $section_json = $this->fetch_section(intval($row['id_sections']));
            if (!isset($section_json['section'])) {
                continue;
            }
            $section = $section_json['section'];
            $section['position'] = intval($row['position']);
            $this->attach_nav_children($section);
            $json['page']['sections'][] = $section;
        }
        if ($page['id_navigation_section']) {
            $nav_json = $this->fetch_section(intval($page['id_navigation_section']));
            if (isset($nav_json['section'])) {
                $nav_section = $nav_json['section'];
                $this->attach_nav_children($nav_section);
                $json['page']['navigation_section'] = $nav_section;
            }
        }
        return $json;
    }

    /* Public Methods *********************************************************/

    /**
     * Export the selected section or page and save the data in the prepert json
     * @retval boolean return true if the export is correct and false if the export failed.
     */
    public function export_json()
    {
        $this->json = null;
        $schema_file = null;
        $file_name = PROJECT_NAME . '_' . $this->type . '_' . $this->id;
        try {
            if ($this->type == 'section' && $this->id > 0) {
                if (!$this->can_export_section($this->id)) {
                    $this->json = 'Error: You do not have access to this section.';
                    return false;
                }
                $this->json = $this->fetch_section($this->id);
                $schema_file = 'section.json';
            } else if ($this->type == 'page' && $this->id > 0) {
                if (!$this->acl->has_access_select($_SESSION['id_user'], $this->id)) {
                    $this->json = 'Error: You do not have access to this page.';
                    return false;
                }
                $this->json = $this->fetch_page($this->id);
                $schema_file = 'page.json';
                if ($this->json) {
                    $file_name = PROJECT_NAME . '_page_' . $this->json['page']['keyword'];
                }
            }
        } catch (Exception $e) {
            // e.g. a circular reference in the sections
            $this->json = 'Error: ' . $e->getMessage();
            return false;
        }
        if ($schema_file === null) {
            $this->json = 'Error: Unknown export type.';
            return false;
        }
        if (!$this->json) {
            $this->json = 'Error: The ' . $this->type . ' was not found.';
            return false;
        }
        $this->json['file_name'] = $file_name;
        $this->json['time'] = date("Y-m-d H:i:s");
        $this->json['platform'] = PROJECT_NAME;
        $this->json['version'] = array(
            "application" => $this->db->get_git_version(__DIR__),
            "database" => $this->db->query_db_first('SELECT version FROM version')['version']
        );
        $schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/../../../schemas/' . $schema_file)));
        try {
            $validate = json_decode(json_encode($this->json), FALSE);
            $schema->in($validate);
        } catch (Exception $e) {
            $this->json = 'Error: ' .  $e->getMessage();
            return false;
        }
        return true;
    }
}
