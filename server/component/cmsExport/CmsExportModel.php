<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

use Swaggest\JsonSchema\Schema;

require_once __DIR__ . "/../BaseModel.php";
require_once __DIR__ . "/../../service/ext/swaggest_json_schema_0.12.31.0_require/vendor/autoload.php";

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
        $sql = "SELECT parent, child, sh.position, parent.`name` as psarent_section_name, children.section_name as children_section_name,
                content, style_name, field_name, locale, gender, children.id_styles, children.id_fields
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
                $child['id_sections'] = intval($field['parent']);
                $child['style_name'] = $field['style_name'];
                $child['id_styles'] = intval($field['id_styles']);
                $child['position'] = intval($field['position']);
                $child['fields'] = array(); //initalize empty array for the section fields
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
            );
        }
        if (count($child) > 0) {
            // add the child only if exists
            $children[] = $child;
        }
        return $children;
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
            );
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
        if ($this->type == 'section' && $this->id > 0) {
            $this->json = $this->fetch_section($this->id);
        }
        $this->json['file_name'] = PROJECT_NAME . '_' . $this->type . '_' . $this->id;
        $this->json['time'] = date("Y-m-d H:i:s");
        $this->json['platform'] = PROJECT_NAME;
        $this->json['version'] = array(
            "application" => rtrim(shell_exec("git describe --tags")),
            "database" => $this->db->query_db_first('SELECT version FROM version')['version']
        );
        if ($this->type == 'section') {
            $schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/../../schemas/section.json')));
        } else {
            $schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/../../schemas/page.json')));
        }
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
