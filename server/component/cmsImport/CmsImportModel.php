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
class CmsImportModel extends BaseModel
{

    /* Public Properties *****************************************************/

    /**
     * Page or section
     */
    public $type;

    /**
     * The imported json
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
    public function __construct($services, $type)
    {
        parent::__construct($services);
        $this->type = $type;
    }

    /* Private Methods ********************************************************/

    /**
     * Insert the section in the database
     * @param array $section 
     * the section info
     * @param integer $parent
     * the parent_id if the section is a child
     * @retval integer 
     * returns the inserted section_id
     * If there is an error throw an exception which rollback the inserts
     */
    private function insert_section($section, $parent = null)
    {
        $section_style_id = $this->db->query_db_first('SELECT id FROM styles WHERE name = :name', array(":name" => $section['style_name']))['id'];
        $id_sections = $this->db->insert("sections", array(
            "id_styles" => $section_style_id,
            "name" => $section['section_name'] . '_' . time(),
        ));
        foreach ($section['fields'] as $key => $field) {
            $id_fields = $this->db->query_db_first('SELECT id FROM fields WHERE name = :name', array(":name" => $field['field_name']))['id'];
            $id_languages = $this->db->query_db_first('SELECT id FROM languages WHERE locale = :locale', array(":locale" => $field['locale']))['id'];
            $id_genders = $this->db->query_db_first('SELECT id FROM genders WHERE name = :name', array(":name" => $field['gender']))['id'];
            if (!$this->db->insert("sections_fields_translation", array(
                "id_sections" => $id_sections,
                "id_fields" => $id_fields,
                "id_languages" => $id_languages,
                "id_genders" => $id_genders,
                "content" => $field['content']
            ))) {
                throw new Exception('Field cannot be imported. JSON: ' . json_encode($field));
            }
        }
        if ($parent > 0) {
            // this is a child section; insert into sections_hierarchy
            if (!$this->db->insert("sections_hierarchy", array(
                "parent" => $parent,
                "child" => $id_sections,
                "position" => $section['position']
            ))) {
                throw new Exception('Cannot insert child section:  ' . $section['section_name']);
            }
        }
        foreach ($section['children'] as $key => $child) {
            $this->insert_section($child, $id_sections);
        }
        return $id_sections;
    }

    /* Public Methods *********************************************************/

    /**
     * Validate the json file against the schema and if it is valid we save it
     * @param string $json_string json data as string
     * @retval boolean true or false if the file is invalid
     */
    public function validate_and_set_json($json_string)
    {
        if ($this->type == 'section') {
            $schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/../../schemas/section.json')));
        } else {
            $schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/../../schemas/page.json')));
        }
        try {
            $validate = json_decode($json_string, FALSE);
            $schema->in($validate);
            $this->json = json_decode($json_string, TRUE);
            return true;
        } catch (Exception $e) {
            $this->json = 'JSON parse error: ' .  $e->getMessage();
            return false;
        }
    }

    /**
     * Import section based on the json input file
     */
    public function import_section()
    {
        try {
            $this->db->begin_transaction();
            $section_id = $this->insert_section($this->json['section']);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return $e->getMessage();
        }
    }
}
