<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../formUserInput/FormUserInputModel.php";
/**
 * This class is used to prepare all data related to the messageBoard style
 * component such that the data can easily be displayed in the view of the
 * component.
 */
class MessageBoardModel extends FormUserInputModel
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'name' (empty string).
     * The name of the form to store the replies to the score.
     */
    private $name;

    /**
     * DB field 'form_name' (empty string).
     * The name of the form which was used to store the score.
     */
    private $form_name;

    /**
     * The section ID of the reply input field.
     */
    private $reply_input_section_id;

    /**
     * The section ID of the link input field holding the sore record ID.
     */
    private $link_input_section_id;

    /**
     * The name of the reply input field.
     */
    private $reply_input_name;

    /**
     * The name of the link input field holding the score record ID.
     */
    private $link_input_name;

    /**
     * The name of the score input field where the score value is stored.
     */
    private $score_input_name;


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
        $this->form_name = $this->get_db_field("form_name", null);
        $this->name = $this->get_db_field("name");
        $this->reply_input_name = $this->name . "_reply";
        $this->link_input_name = $this->name . "_link";
        $this->score_input_name = $this->form_name . "_publish";
        $this->init_children_ids($id);
    }

    /* Private Methods ********************************************************/

    /**
     * Appends sections of type input as children the very first time this
     * section is loaded.
     *
     * @param int $id
     *  The ID of the parent section
     * @param int $id_style
     *  The ID of the style of the section to be appended as child.
     * @param int $id_field
     *  The ID of the field `name` which will be set with the name of the input
     *  field.
     * @param string $name
     *  The name of the input field.
     * @retval int
     *  The ID of the new section.
     */
    private function add_section_child($id, $id_style, $id_field, $name)
    {
        $new_id = $this->db->insert("sections", array(
            "name" => $name,
            "id_styles" => $id_style
        ));
        $this->db->insert("sections_hierarchy", array(
            "parent" => $id,
            "child" => $new_id
        ));

        return $new_id;
    }

    /**
     * Sets the content of a field and associates with a section.
     *
     * @param int $id
     *  The ID of the section to associate the field with
     * @param int $id_field
     *  The ID of the field
     * @param any $content
     *  The content to which the field will be set
     */
    private function add_section_field($id, $id_field, $content)
    {
        $this->db->insert("sections_fields_translation", array(
            "id_sections" => $id,
            "id_fields" => $id_field,
            "content" => $content
        ));
    }

    /**
     * Dynamically append two sections of style input the very first time this
     * Style is loaded. Further, the input id properties are set.
     *
     * @param int $id
     *  The ID of this section.
     */
    private function init_children_ids($id)
    {
        foreach($this->children as $input)
        {
            $section_name = $input->get_model()->get_section_name();
            if($section_name == $this->section_name . "_link")
            {
                $this->link_input_section_id = $input->get_id_section();
            }
            else if($section_name == $this->section_name . "_reply")
            {
                $this->reply_input_section_id = $input->get_id_section();
            }
        }
    }

    /**
     * Fetch all replies of a given record.
     *
     * @param int $record_id
     *  The ID of the score record for which the replies will be fetched.
     * @retval array
     *  An array of replies with the following keys:
     *   - `user_id`: The user ID of the replier
     *   - `user_name`: The user name of the replier
     *   - `value`: The reply message
     *   - `create_time`: The time of creation of the reply record
     */
    private function fetch_replies($record_id)
    {
        $sql = "SELECT vui.user_id, vui.user_name, vui.value, uir.create_time FROM `view_user_input` AS vui
            LEFT JOIN `view_user_input` AS vui2
            ON vui.record_id = vui2.record_id
            LEFT JOIN user_input_record AS uir ON vui.record_id = uir.id
            WHERE vui.form_name = :form_name
            AND vui.field_name = :field_name
            AND vui2.value = :record_id";
        return $this->db->query_db($sql, array(
            ":record_id" => $record_id,
            ":form_name" => $this->name,
            ":field_name" => $this->reply_input_name
        ));
    }

    /* Public Methods *********************************************************/

    /**
     * Overwrites the parent definition. This is called after the creation of
     * the section. Here the hidden style fields are added and preset.
     */
    public function cms_post_create_callback($cms_model, $section_name,
        $section_style_id, $relation, $id)
    {
        $id_field = $this->db->fetch_field_id_by_name("is_log");
        $this->add_section_field($id, $id_field, 1);

        $id_field = $this->db->fetch_field_id_by_name("name");
        $this->add_section_field($id, $id_field, "");

        $id_style = $this->db->fetch_style_id_by_name("input");
        $this->link_input_section_id = $this->add_section_child(
            $this->section_id, $id_style, $id_field,
            $this->section_name . "_link");
        $this->add_section_field($this->link_input_section_id, $id_field, "");

        $this->reply_input_section_id = $this->add_section_child(
            $this->section_id, $id_style, $id_field,
            $this->section_name . "_reply");
        $this->add_section_field($this->reply_input_section_id, $id_field, "");
    }

    /**
     * Overwrites the parent definition. This is called after the creation of
     * the section. Here the hidden style fields are added and preset.
     */
    public function cms_pre_update_callback($cms_model, $data)
    {
        if(!isset($data['form_name'][ALL_LANGUAGE_ID][MALE_GENDER_ID]['content'])) {
            return;
        }
        $new_form_name = $data['form_name'][ALL_LANGUAGE_ID][MALE_GENDER_ID]['content'];
        if($new_form_name !== $this->form_name) {
            $id_field = $this->db->fetch_field_id_by_name("name");
            $cms_model->update_section_fields_db($id_field, ALL_LANGUAGE_ID,
                MALE_GENDER_ID, $new_form_name . "_replies", $this->section_id);
            $cms_model->update_section_fields_db($id_field, ALL_LANGUAGE_ID,
                MALE_GENDER_ID, $new_form_name . "_replies_link",
                $this->link_input_section_id);
            $cms_model->update_section_fields_db($id_field, ALL_LANGUAGE_ID,
                MALE_GENDER_ID, $new_form_name . "_replies_reply",
                $this->reply_input_section_id);
        }
    }

    /**
     * Get all score records which were submitted by all users.
     *
     * @retval array
     *  An array of score entries:
     *   - `user_id`: The ID of the user who submitted the score.
     *   - `user_name`: The name of the user who submitted the score.
     *   - `create_time`: The time of creation of the score record.
     *   - `value`: The score value.
     *   - `record_id`: The ID of the score record.
     */
    public function get_scores($limit)
    {
        $sql = "SELECT * FROM (
                SELECT ui.user_id, ui.user_name, uir.create_time, ui.value, ui.record_id
                FROM view_user_input AS ui
                LEFT JOIN user_input_record AS uir ON ui.record_id = uir.id
                WHERE form_name = :form_name AND field_name = :field_name
                ORDER BY ui.record_id DESC";

        if($limit) {
            $sql .= " LIMIT $limit";
        }

        $sql .= ") AS T1 ORDER BY T1.record_id";

        return $this->db->query_db($sql, array(
            ":form_name" => $this->form_name,
            ":field_name" => $this->score_input_name
        ));
    }

    /**
     *
     */
    public function get_replies($record_id, $icons, $enable_counts = true)
    {
        $replies = $this->fetch_replies($record_id);
        if(!$enable_counts) {
            return array(
                "reply_messages" => $replies,
                "icon_counter" => array()
            );
        }

        $filtered_replies = array();
        $icon_counter = array();

        foreach($icons as $icon) {
            $icon_counter[$icon] = array(
                "count" => 0,
                "users" => array(),
                "user_names" => array(),
                "disabled" => false
            );
        }

        foreach ($replies as $reply) {
            if (in_array($reply['value'], $icons)) {
                $icon_counter[$reply['value']]['count']++;
                array_push($icon_counter[$reply['value']]['users'], $reply['user_id']);
                array_push($icon_counter[$reply['value']]['user_names'], $reply['user_name']);
                if(in_array($_SESSION['id_user'], $icon_counter[$reply['value']]['users'])){
                    $icon_counter[$reply['value']]['disabled'] = true;
                };
            } else {
                array_push($filtered_replies, $reply);
            }
        }

        return array(
            "reply_messages" => $filtered_replies,
            "icon_counter" => $icon_counter
        );
    }

    public function convert_timestamp($ts_str)
    {
        $now = new DateTime('now');
        $ts = new DateTime($ts_str);
        $diff = date_diff($now, $ts);
        if($diff->y)
            return $diff->y . " year" . ($diff->y == 1 ? "" : "s") . " ago";
        if($diff->m)
            return $diff->m . " month" . ($diff->m == 1 ? "" : "s") . " ago";
        if($diff->d)
            return $diff->d . " day" . ($diff->d == 1 ? "" : "s") . " ago";
        if($diff->h)
            return $diff->h . " hour" . ($diff->h == 1 ? "" : "s") . " ago";
        if($diff->i)
            return $diff->i . " minute" . ($diff->i == 1 ? "" : "s") . " ago";
        return "just now";
    }

    /**
     * Get the name of the reply form.
     *
     * @retval string
     *  The name of the reply form.
     */
    public function get_form_name()
    {
        return $this->name;
    }

    /**
     * Get the ID of the reply input section.
     *
     * @retval int
     *  The ID of the reply input section.
     */
    public function get_reply_input_section_id()
    {
        return $this->reply_input_section_id;
    }

    /**
     * Get the ID of the link input section.
     *
     * @retval int
     *  The ID of the link input section.
     */
    public function get_link_input_section_id()
    {
        return $this->link_input_section_id;
    }

    /**
     * return the avatar
     * @param int $user_id 
     * @retval string
     * the avatar
     */
    public function get_avatar($user_id){
        return $this->db->get_avatar($user_id);
    }
}
