<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the messageBoard style
 * component such that the data can easily be displayed in the view of the
 * component.
 */
class MessageBoardModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'name' (empty string).
     * The name of the form which was used to store the score.
     */
    private $name;

    /* private $form_section_id; */
    private $reply_input_section_id;
    private $link_input_section_id;


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $this->name = $this->get_db_field("name");
        $this->init_children($id);
    }

    /* Private Methods ********************************************************/

    private function add_child_input_section($id, $id_style, $id_field, $name)
    {
        $field_content = $this->name . "_" . $name;
        $name_section = $field_content . "_input";
        $new_id = $this->db->insert("sections", array(
            "name" => $name_section,
            "id_styles" => $id_style
        ));
        $this->db->insert("sections_hierarchy", array(
            "parent" => $id,
            "child" => $new_id
        ));
        $this->db->insert("sections_fields_translation", array(
            "id_sections" => $new_id,
            "id_fields" => $id_field,
            "content" => $field_content
        ));

        return $new_id;
    }

    private function init_children($id)
    {
        $name_reply_input_section = $this->name . "_reply_input";
        $name_link_input_section = $this->name . "_link_input";
        if( count($this->children ) == 0 )
        {
            $id_field = $this->db->fetch_field_id_by_name("name");
            $id_style = $this->db->fetch_style_id_by_name("input");
            $this->link_input_section_id = $this->add_child_input_section($id,
                $id_style, $id_field, "link");
            $this->reply_input_section_id = $this->add_child_input_section($id,
                $id_style, $id_field, "reply");
        }
        else
        {
            foreach($this->children as $input)
            {
                $section_name = $input->get_model()->get_section_name();
                if($section_name == $name_link_input_section)
                {
                    $this->link_input_section_id = $input->get_id_section();
                }
                else if($section_name == $name_reply_input_section)
                {
                    $this->reply_input_section_id = $input->get_id_section();
                }
            }
        }
    }

    public function fetch_replies($record_id)
    {
        $sql = "SELECT vui.user_name, vui.value, uir.create_time FROM `view_user_input` AS vui
            LEFT JOIN `view_user_input` AS vui2
            ON vui.record_id = vui2.record_id
            LEFT JOIN user_input_record AS uir ON vui.record_id = uir.id
            WHERE vui.form_name = 'score'
            AND vui.field_name = 'score_reply'
            AND vui2.value = :record_id";
        return $this->db->query_db($sql, array(":record_id" => $record_id));
    }

    /* Public Methods *********************************************************/

    public function get_scores()
    {
        $sql = "SELECT ui.user_id, ui.user_name, uir.create_time, ui.value, ui.record_id
            FROM view_user_input AS ui
            LEFT JOIN user_input_record AS uir ON ui.record_id = uir.id
            WHERE form_name = :form_name AND field_name = :field_name";
        return $this->db->query_db($sql, array(
            ":form_name" => $this->name,
            ":field_name" => $this->name . "_publish"
        ));
    }

    public function get_replies($record_id)
    {
        $replies = $this->fetch_replies($record_id);
        return $replies;
    }

    public function get_icon_count($icon, $record_id)
    {
        $sql = "SELECT ui.user_id, ui.user_name, uir.create_time, ui.value
            FROM view_user_input AS ui
            LEFT JOIN user_input_record AS uir ON ui.record_id = uir.id
            WHERE form_name = :name AND field_name = 'score_' and user_id";
        return $this->db->query_db($sql, array(":name" => $this->name . "_" . $icon ));
    }

    public function get_form_name()
    {
        return $this->name;
    }

    public function get_reply_input_section_id()
    {
        return $this->reply_input_section_id;
    }

    public function get_link_input_section_id()
    {
        return $this->link_input_section_id;
    }
}
