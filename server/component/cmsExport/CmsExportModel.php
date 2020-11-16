<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
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
        $sql = "SELECT parent, child, parent.name as psarent_section_name, children.section_name as children_section_name,
                content, style_name, field_name, locale, gender, children.id_styles, children.id_fields
                FROM sections_hierarchy sh
                INNER JOIN sections parent ON (sh.parent = parent.id)
                INNER JOIN view_sections_fields children ON (sh.child = children.id_sections)
                WHERE sh.parent = :id_sections";
        $section_sql = $this->db->query_db($sql, array(":id_sections" => $parent_id));
        $section = array();
        foreach ($section_sql as $row => $field) {
            if (!isset($section[$field['children_section_name']])) {
                // the section is not yet defined
                $section[$field['children_section_name']] = array();
                $section[$field['children_section_name']]['id_sections'] = $field['parent'];
                $section[$field['children_section_name']]['fields'] = array(); //initalize empty array for the section fields
                $section[$field['children_section_name']]['children'] = $this->fetch_section_children($field['child']);
            }
            $section[$field['children_section_name']]['fields'][] = array(
                "id_styles" => $field['id_styles'],
                "style_name" => $field['style_name'],
                "field_name" => $field['field_name'],
                "id_fields" => $field['id_fields'],
                "locale" => $field['locale'],
                "gender" => $field['gender'],
                "content" => $field['content'],
            );
        }
        return $section;
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
        foreach ($section_sql as $row => $field) {
            if (!isset($section[$field['section_name']])) {
                // the section is not yet defined
                $section[$field['section_name']] = array();
                $section[$field['section_name']]['id_sections'] = $field['id_sections'];
                $section[$field['section_name']]['fields'] = array(); //initalize empty array for the section fields
                $section[$field['section_name']]['children'] = $this->fetch_section_children($field['id_sections']);
            }
            $section[$field['section_name']]['fields'][] = array(
                "id_styles" => $field['id_styles'],
                "style_name" => $field['style_name'],
                "field_name" => $field['field_name'],
                "id_fields" => $field['id_fields'],
                "locale" => $field['locale'],
                "gender" => $field['gender'],
                "content" => $field['content'],
            );
        }
        return $section;
    }

    /* Public Methods *********************************************************/

    public function export_json()
    {
        $json = null;
        if ($this->type == 'section' && $this->id > 0) {
            $json = $this->fetch_section($this->id);
        }
        $json['file_name'] = $this->type . '_' . $this->id;
        $json['time'] = date("Y-m-d H:i:s");
        $json['platform'] = PROJECT_NAME;
        $json['version'] = array(
            "application" => rtrim(shell_exec("git describe --tags")),
            "database" => $this->db->query_db_first('SELECT version FROM version')['version']
        );
        return $json; 
    }
}
