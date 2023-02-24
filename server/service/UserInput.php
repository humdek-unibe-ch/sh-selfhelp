<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * Class to deal with user inputs.
 */
class UserInput
{
    /* Private Properties *****************************************************/

    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * The transaction instance that log to DB.
     */
    private $transaction;

    /**
     * The collection of input field attributes. See UserInput::set_field_attrs.
     */
    private $field_attrs = NULL;

    /**
     * Array that contains the ui preference settings for the user
     */
    private $ui_pref;

    /* Constructors ***********************************************************/

    /**
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($db, $transaction)
    {
        $this->db = $db;
        $this->transaction = $transaction;
        $this->db->get_cache()->clear_cache($this->db->get_cache()::CACHE_TYPE_USER_INPUT);
    }

    /* Private Methods ********************************************************/

    /**
     * Fetches all user input fields from the database given certain conditions.
     *
     * @param array $conds
     *  A key => value array of db conditions where the key corresponds to the
     *  db column and the value to the db value.
     * @param boolean $get_page_info
     * If true it fetch the info for the page and nav 
     * @retval array
     *  An array of field items where eeach item has the following keys:
     *  - 'id'            A unique id of the field
     *  - 'user_code'     A unique string that connects values to a user without
     *                    revealing the identity of the user.
     *  - 'user_gender'   The gender of the user.
     *  - 'page'          The keyword of the page where the data was entered.
     *  - 'nav'           The name of the navigation section where the data was
     *                    entered.
     *  - 'field_name'    The name of the input field.
     *  - 'field_label'   The label of the input field.
     *  - 'field_type'    The type of the input field. This is either the name
     *                    of the form field style or if the style is 'input' the
     *                    input type.
     *  - 'value'         The value that was entered by the user.
     *  - 'timestamp'     The date and time when the value was entered.
     *  - 'id_user_input_record' The new field that keep the rows
     */
    private function fetch_input_fields($conds = array(), $get_page_info = false)
    {
        // rework
        if(!isset($conds['ui.id_section_form']))
            $field_attrs = $this->get_field_attrs(-1, $get_page_info);
        $sql = "SELECT ui.id, ui.id_users, ui.value, ui.edit_time, ui.id_sections,
            g.name AS gender, vc.code, id_user_input_record
            FROM user_input AS ui
            LEFT JOIN users AS u ON u.id = ui.id_users
            LEFT JOIN genders AS g ON g.id = u.id_genders
            LEFT JOIN validation_codes AS vc on vc.id_users = ui.id_users
            WHERE 1";
        $gender = $_SESSION['gender'];
        $language = $_SESSION['language'];
        foreach($conds as $key => $value)
        {
            if($key === "g.name") $gender = $value;

            $sql .= " AND " . $key . " = '" . $value . "'";
        }
        $fields_db = $this->db->query_db($sql);

        $fields = array();
        foreach($fields_db as $field)
        {
            $id = intval($field["id_sections"]);
            if (isset($conds['ui.id_section_form'])) {
                $field_attrs = $this->get_field_attrs($id, $get_page_info);
            }
            if(!isset($field_attrs[$id])) continue;
            $field_label = $field_attrs[$id]["label"][$gender][$language] ?? "";
            if($gender === "female" && $field_label === "")
                $field_label = $field_attrs[$id]["label"]["male"][$language] ?? "";
            $fields[] = array(
                "id" => $field['id'],
                "user_code" => $field['code'],
                "user_gender" => $field['gender'],
                "page" => $field_attrs[$id]["page"],
                "nav" => $field_attrs[$id]["nav"],
                "field_name" => $field_attrs[$id]["name"],
                "field_label" => $field_label,
                "field_type" => $field_attrs[$id]["type"],
                "form_name" => $field_attrs[$id]["form_name"],
                "value" => $field["value"],
                "timestamp" => $field["edit_time"],
                "id_user_input_record" => $field["id_user_input_record"],
            );
        }
        return $fields;
    }

    /**
     * Fetch the page name to which the given navigation section belongs.
     *
     * @param int $id_section
     *  The id of the section
     * @return string
     *  The page name or null if the name could not be found.
     */
    private function fetch_nav_section_page($id_section)
    {
        $sql = "SELECT p.keyword FROM sections_navigation AS sn
            LEFT JOIN pages AS p ON p.id = sn.id_pages
            WHERE sn.child = :id";
        $page = $this->db->query_db_first($sql, array(":id" => $id_section));
        if($page) return $page["keyword"];
        else return null;
    }

    /**
     * Fetch the name of a section.
     *
     * @param int $id
     *  The id of the section
     * @return string
     *  The section name or null if the name could not be found.
     */
    private function fetch_section_name($id)
    {
        $sql = "SELECT `name` FROM sections WHERE id = :id";
        $parent = $this->db->query_db_first($sql, array(":id" => $id));
        if($parent) return $parent["name"];
        else return null;
    }

    /**
     * Fetch the page name to which the given section belongs.
     *
     * @param int $id_section
     *  The id of the section
     * @return string
     *  The page name or null if the name could not be found.
     */
    private function fetch_section_page($id_section)
    {
        $sql = "SELECT p.keyword FROM pages_sections AS ps
            LEFT JOIN pages AS p ON p.id = ps.id_pages
            WHERE ps.id_sections = :id";
        $page = $this->db->query_db_first($sql, array(":id" => $id_section));
        if($page) return $page["keyword"];
        else return null;
    }

    /**
     * Fetch the id of the parent section.
     *
     * @param int $id_child
     *  The id of the child section.
     * @retval int
     *  The id of the parent section or null if no parent could be found.
     */
    private function fetch_section_parent($id_child)
    {
        $sql = "SELECT parent FROM sections_hierarchy WHERE child = :id";
        $parent = $this->db->query_db_first($sql, array(":id" => $id_child));
        if($parent) return intval($parent["parent"]);
        else return null;
    }

    /**
     * Find the page name and navigation section name of a given child section.
     *
     * @param int $id_section
     *  The id of the child section.
     * @retval array
     *  An array with the keys "page" and "nav" where the former holds the name
     *  of the parent page and the latter the name of the parent navigation
     *  section.
     */
    private function find_section_page($id_section)
    {
        $page = null;
        $nav = null;
        $parent_it = $this->fetch_section_parent($id_section);
        $parent = $parent_it;
        while($parent_it !== null)
        {
            $parent = $parent_it;
            $parent_it = $this->fetch_section_parent($parent_it);
        }
        if($parent !== null)
        {
            $page = $this->fetch_section_page($parent);
            if($page === null)
            {
                $page = $this->fetch_nav_section_page($parent);
                $nav = $this->fetch_section_name($parent);
            }
        }
        return array("page" => $page, "nav" => $nav);
    }

    /* Public Methods *********************************************************/

    /**
     * Convert a string to HTML valid id
     *
     * @param string $string
     *  the string value that we want to convert to a valid HTML id
     * @retval string
     * the converted string which will be used as ID
     */
     public function convert_to_valid_html_id($string){
        //Lower case everything
         $string = strtolower($string);
         //Make alphanumeric (removes all other characters)
         $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
         //Clean up multiple dashes or whitespaces
         $string = preg_replace("/[\s-]+/", " ", $string);
         //Convert whitespaces and underscore to dash
         $string = preg_replace("/[\s_]/", "-", $string);
         return $string;        
     }

    /**
     * Get all input fields given a filter
     *
     * @param array $filter
     *  The filter array can be empty or have any of the following keys:
     *   - 'id'           Selects a field with a given id.
     *   - 'gender'       This can either be set to 'male' or 'female'.
     *   - 'field_name'   Selects all fields with the given name.
     *   - 'form_name'    Selects all fields from the given form name.
     *   - 'page'         Selects all fields on a given page.
     *   - 'nav'          Selects all fields in a given navigation sections.
     *   - 'id_section'   Selects all fields with given section id.
     *   - 'id_user'      Selects all fields from a given user id.
     *   - 'removed'      Selects all fields matching the removed flag
     * @param boolean $get_page_info
     * If true it fetch the info for the page and nav 
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields($filter = array(), $get_page_info = false)
    {
        // rework
        $db_cond = array();
        if(isset($filter["gender"]))
            $db_cond["g.name"] = $filter["gender"];
        if(isset($filter["id_section"]))
            $db_cond["ui.id_sections"] = $filter["id_section"];
        if(isset($filter["id_section_form"])) {
            $db_cond["ui.id_section_form"] = $filter["id_section_form"];
        }
        if(isset($filter["id_user"]))
            $db_cond["ui.id_users"] = $filter["id_user"];
        if(isset($filter["id"]))
            $db_cond["ui.id"] = $filter["id"];
        if(isset($filter["removed"]))
            $db_cond["ui.removed"] = $filter["removed"] ? '1' : '0';
        if(isset($filter["form_name"]))
            $db_cond["ui.id_section_form"] = $this->get_form_id($filter["form_name"]);
        $fields_all = $this->fetch_input_fields($db_cond, $get_page_info);
        $fields = array();
        foreach($fields_all as $field)
            if((!isset($filter["field_name"]) || (isset($filter["field_name"])
                        && $field['field_name'] === $filter["field_name"]))
                && (!isset($filter["page"]) || (isset($filter["page"])
                        && $field['page'] === $filter["page"]))
                && (!isset($filter["nav"]) || (isset($filter["nav"])
                        && strpos($field['nav'], $filter["nav"]) !== false))
            )
                $fields[] = $field;
        return $fields;
    }

    /**
     * Get all input fields submitted by male users.
     *
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_gender_male()
    {
        return $this->get_input_fields(array("gender" => "male"));
    }

    /**
     * Get all input fields submitted by female users.
     *
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_gender_female()
    {
        return $this->get_input_fields(array("gender" => "female"));
    }

    /**
     * Get all input fields that match a field section id.
     *
     * @param int $field_id
     *  The field_id to match.
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_field_id($field_id)
    {
        return $this->get_input_fields(array("id_sections" => $field_id));
    }

    /**
     * Get all input fields that match a field name.
     *
     * @param string $field_name
     *  The field_name to match.
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_field_name($field_name)
    {
        return $this->get_input_fields(array("field_name" => $field_name));
    }

    /**
     * Get all input fields that are placed on a given page.
     *
     * @param string $keyword
     *  The page keyword to match.
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_page($keyword)
    {
        return $this->get_input_fields(array("page" => $keyword));
    }

    /**
     * Get all input fields that are placed on a given navigation section.
     *
     * @param string $name
     *  The navigation section name to match. All navigation sections containing
     *  the given name are considered.
     * @retval array
     *  The selected user input fields. See UserInput::fetch_input_fields() for
     *  more details.
     */
    public function get_input_fields_by_nav($name)
    {
        return $this->get_input_fields(array("nav" => $name));
    }

    /**
     * Get the user input value of an input field specified by a pattern.
     *
     * @param string $pattern
     *  A field identifier of the form `@<form_name>#<field_name>`.
     * @param int $uid
     *  The id of a user.
     * @retval mixed
     *  On success, the value corresponding to the requested form field, null in
     *  case of a bad pattern syntax, and the empty string if no value was found.
     */
    public function get_input_value_by_pattern($pattern, $uid)
    {
        $names = explode('#', $pattern);
        if(count($names) !== 2)
            return null;

        $form = substr($names[0], 1);
        $field = $names[1];
        $vals = $this->get_input_fields(array(
            "form_name" => $form,
            "field_name" => $field,
            "id_user" => $uid
        ));
        if(count($vals) > 0)
            return end($vals)['value'];

        return "";
    }

    /**
     * Returns the regular expression to find a form field
     *
     * @retval string the regular expression that finds a field identifier of
     * the form `@<form_name>#<field_name>`.
     */
    public function get_input_value_pattern()
    {
        return '@[^"@#]+#[^"@#]+';
    }

    /**
     * Collect attributes for each existing user input field.
     * The following attributes are set:
     *  - 'page'  The name of the parent page of the field.
     *  - 'nav'   The name of the parent navigation section
     *  - 'name'  The name of the field
     *  - 'type'  The type of the field
     */
    public function set_field_attrs()
    {
        $this->field_attrs = array();
        $sql = "SELECT DISTINCT ui.id_sections, sft_it.content AS input_type, sft_in.content AS field_name, st.name AS field_type, sft_if.content AS form_name, sft_il.content AS field_label, g.name AS gender, l.locale AS language FROM user_input AS ui
            LEFT JOIN sections_fields_translation AS sft_it ON sft_it.id_sections = ui.id_sections AND sft_it.id_fields = " . TYPE_INPUT_FIELD_ID . "
            LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = " . NAME_FIELD_ID . "
            LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = " . NAME_FIELD_ID . "
            LEFT JOIN sections_fields_translation AS sft_il ON sft_il.id_sections = ui.id_sections AND sft_il.id_fields = " . LABEL_FIELD_ID . "
            LEFT JOIN sections AS s ON s.id = ui.id_sections
            LEFT JOIN styles AS st ON st.id = s.id_styles
            LEFT JOIN genders AS g ON g.id = sft_il.id_genders
            LEFT JOIN languages AS l ON l.id = sft_il.id_languages";
        $sections = $this->db->query_db($sql);
        foreach($sections as $section)
        {
            $id = intval($section['id_sections']);
            $name = $section['field_name'];
            $label_name = $section['field_label'] ?? $name;
            if(isset($this->field_attrs[$id]))
            {
                $this->field_attrs[$id]["label"][$section['gender']][$section['language']] = $label_name;
                continue;
            }
            $type = $section['input_type'] ?? $section['field_type'];
            $label = array('male' => array(), 'female' => array());
            $label[$section['gender']][$section['language']] = $label_name;
            $page = $this->find_section_page($id);
            $this->field_attrs[$id] = array(
                "page" => $page["page"],
                "nav" => $page["nav"],
                "name" => $name,
                "label" => $label,
                "form_name" => $section['form_name'],
                "type" => $type,
            );
        }
    }

    /**
     * @param $id
     * the id of the input field section
     * @param boolean $get_page_info
     * If true it fetch the info for the page and nav 
     *@retval array
     * Collect attributes for each existing user input field.
     * The following attributes are set:
     *  - 'page'  The name of the parent page of the field.
     *  - 'nav'   The name of the parent navigation section
     *  - 'name'  The name of the field
     *  - 'type'  The type of the field
     */
    public function get_field_attrs($id, $get_page_info = false)
    {
        $field_attrs = array();
        $sql = "SELECT DISTINCT ui.id_sections, sft_it.content AS input_type, sft_in.content AS field_name, st.name AS field_type, sft_if.content AS form_name, sft_il.content AS field_label, g.name AS gender, l.locale AS language FROM user_input AS ui
            LEFT JOIN sections_fields_translation AS sft_it ON sft_it.id_sections = ui.id_sections AND sft_it.id_fields = " . TYPE_INPUT_FIELD_ID . "
            LEFT JOIN sections_fields_translation AS sft_in ON sft_in.id_sections = ui.id_sections AND sft_in.id_fields = " . NAME_FIELD_ID . "
            LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = " . NAME_FIELD_ID . "
            LEFT JOIN sections_fields_translation AS sft_il ON sft_il.id_sections = ui.id_sections AND sft_il.id_fields = " . LABEL_FIELD_ID . "
            LEFT JOIN sections AS s ON s.id = ui.id_sections
            LEFT JOIN styles AS st ON st.id = s.id_styles
            LEFT JOIN genders AS g ON g.id = sft_il.id_genders
            LEFT JOIN languages AS l ON l.id = sft_il.id_languages
            WHERE ui.id_sections = :id or :id = -1";
        $sections = $this->db->query_db($sql, array(":id"=>$id));
        foreach($sections as $section)
        {
            $id = intval($section['id_sections']);
            $name = $section['field_name'];
            $label_name = $section['field_label'] ?? $name;
            if(isset($field_attrs[$id]))
            {
                $field_attrs[$id]["label"][$section['gender']][$section['language']] = $label_name;
                continue;
            }
            $type = $section['input_type'] ?? $section['field_type'];
            $label = array('male' => array(), 'female' => array());
            $label[$section['gender']][$section['language']] = $label_name;
            if($get_page_info){
                $page = $this->find_section_page($id);
            }
            $field_attrs[$id] = array(
                "page" => ($get_page_info ? $page["page"] : ""),
                "nav" => ($get_page_info ? $page["nav"] : ""),
                "name" => $name,
                "label" => $label,
                "form_name" => $section['form_name'],
                "type" => $type,
            );
        }
        return $field_attrs;
    }

    /**
     * Get the UI preferences row for the user. If it is not set returns false
     * @retval array or false
     * return the UI preferences row or false if it is not set
     */
    public function get_ui_preferences()
    {
        if (!isset($this->ui_pref)) {
            // check the database only once. If it is already assigned do not make a query and just returned the already assigned value
            $form_id = $this->get_form_id('ui-preferences', FORM_DYNAMIC);
            if($form_id){
                $ui_pref = $this->get_data($form_id, '');
                $this->ui_pref = $ui_pref ? $ui_pref[0] : array();
            }
        }
        return $this->ui_pref;
    }

    /**
     * Check if we should load the new UI or load the old UI
     */
    public function is_new_ui_enabled()
    {
        $ui_pref = $this->get_ui_preferences();
        if (!$ui_pref || (isset($ui_pref['old_ui'])  && $ui_pref['old_ui'] != 1)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the id of the table or the form based on the required type
     * @param string $name
     * The name of the form or table     
     * @param int $form_type
     * Dynamic or static form, it loads different table based on this value
     * @retval array
     * the result of the fetched form row
     */
    public function get_form_id($name, $form_type = FORM_DYNAMIC)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_USER_INPUT, $name, [__FUNCTION__, $form_type]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            if ($form_type == FORM_DYNAMIC) {
                $sql = 'select id_section_form as id
                from user_input ui
                inner JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = ui.id_section_form AND sft_if.id_fields = 57
                where sft_if.content = :name
                limit 0,1;';
            } else if ($form_type == FORM_STATIC) {
                $sql = 'SELECT id 
                FROM uploadTables
                WHERE name = :name';
            }
            $res = $this->db->query_db_first($sql, array(":name" => $name));
            $res = $res ? $res['id'] : '';
            $this->db->get_cache()->set($key, $res);
            return $res;
        }        
    }

    /**
     * Fetch the record data
     * @param int $form_id
     * the form id of the form that we want to fetcht
     * @param string $filter
     * filter string that is added to the having clause
     * @param boolean $own_entries_only
     * If true it loads only records created by the same user. 
     * @param string $form_type
     * Dynamic or static form, it loads different table based on this value
     * @param int $user_id
     * Show the data for that user
     * @param boolean $db_first
     * If true it returns the first row. 
     * @retval array
     * the result of the fetched data
     */
    public function get_data($form_id, $filter, $own_entries_only = true, $form_type = FORM_DYNAMIC, $user_id = null, $db_first = false)
    {
        if(strpos($filter, '{{') !== false ){
            $filter = ''; // filter is not correct, tried to be set dynamically but failed
        }
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_USER_INPUT, $form_id, [__FUNCTION__, $filter, $own_entries_only, $form_type, $user_id, $db_first]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            if (!$user_id) {
                $user_id =  $_SESSION['id_user']; // if the user is not defined we set the session user if needed
            }
            if ($form_type == FORM_DYNAMIC) {
                $sql = 'CALL get_form_data_for_user_with_filter(:form_id, :user_id, :filter)';
                $params = array(
                    ":form_id" => $form_id,
                    ":user_id" => $user_id
                );
                if (!$own_entries_only) {
                    $sql = 'CALL get_form_data_with_filter(:form_id, :filter)';
                    $params = array(
                        ":form_id" => $form_id
                    );
                }
            } else if ($form_type == FORM_STATIC) {
                $params = array(
                    ":form_id" => $form_id,
                );
                if ($own_entries_only) {
                    $filter = ' AND id_users = ' . intval($user_id) . ' ' . $filter;
                }
                $sql = 'CALL get_uploadTable_with_filter(:form_id, :filter)';
            }
            $params[':filter'] = $filter;
            if ($db_first) {
                $res = $this->db->query_db_first($sql, $params);
            } else {
                $res = $this->db->query_db($sql, $params);
            }
            $this->db->get_cache()->set($key, $res);
            return $res;
        }    
    }

    /**
     * Fetch the record data for a given user
     * @param int $form_id
     * the form id of the form that we want to fetcht
     * @param int $user_id
     * Show the data for that user
     * @param string $filter
     * filter string that is added to the having clause
     * @param string $form_type
     * Dynamic or static form, it loads different table based on this value
     * @param boolean $db_first
     * If true it returns the first row. 
     * @retval array
     * the result of the fetched data
     */
    public function get_data_for_user($form_id, $user_id, $filter, $form_type = FORM_DYNAMIC, $db_first = false)
    {        
        return $this->get_data($form_id, $filter, true, $form_type, $user_id, $db_first);
    }

    /**
     * Get the avatar of the current user
     *
     * @param int $user_id
     * 
     * @retval string
     *  The avatar image of the current user or emty string.
     */
    public function get_avatar($user_id)
    {
        $form_id = $this->get_form_id('avatar');
        if ($form_id) {
            $avatar = $this->get_data_for_user($form_id, $user_id, '', FORM_DYNAMIC, true);
            return $avatar ? $avatar['avatar'] : '';
        } else {
            return '';
        }
    }

    /**
     * Save static data in the upload_tables structure
     * @param string $transaction_by
     * What initialized the transaction
     * @param string $table_name
     * The table name where we want to save the data
     * @param array $data
     * The data that we want to save - associative array which contains "name of the column" => "value of the column"
     * @return array
     * return array with the result containing result and message
     */
    public function save_static_data($transaction_by, $table_name, $data){
        $data['id_users'] = $_SESSION['id_user'];
        $data['user_code'] = $_SESSION['user_code'];
        $id_table = $this->get_form_id($table_name, FORM_STATIC);
        try {
            $this->db->begin_transaction();
            if (!$id_table) {
                // does not exists yet; try to create it
                $id_table = $this->db->insert("uploadTables", array(
                    "name" => $table_name
                ));
            }
            if (!$id_table) {
                $this->db->rollback();
                return array(
                    "res" => false,
                    "msg" => "postprocess: failed to create new data table"
                );
            } else {
                if ($this->transaction->add_transaction(transactionTypes_insert, $transaction_by, null, $this->transaction::TABLE_uploadTables, $id_table) === false) {
                    $this->db->rollback();
                    return false;
                }
                $id_row = $this->db->insert("uploadRows", array(
                    "id_uploadTables" => $id_table
                ));
                if (!$id_row) {
                    $this->db->rollback();
                    return array(
                        "res" => false,
                        "msg" => "postprocess: failed to add table rows"
                    );
                }
                foreach ($data as $col => $value) {
                    $id_col = $this->db->insert("uploadCols", array(
                        "name" => $col,
                        "id_uploadTables" => $id_table
                    ));
                    if (!$id_col) {
                        $this->db->rollback();
                        return array(
                            "res" => false,
                            "msg" => "postprocess: failed to add table cols"
                        );
                    }
                    $res = $this->db->insert(
                        "uploadCells",
                        array(
                            "id_uploadRows" => $id_row,
                            "id_uploadCols" => $id_col,
                            "value" => $value
                        )
                    );
                    if (!$res) {
                        $this->db->rollback();
                        return array(
                            "res" => false,
                            "msg" => "postprocess: failed to add data values"
                        );
                    }
                }
            }
            $this->db->commit();
            return array(
                "res" => true,
                "msg" => "Record for user : " . $_SESSION['id_user'] . " was successfully inserted in DB"
            );
        } catch (Exception $e) {
            $this->db->rollback();
            return array(
                "res" => false,
                "msg" => "Error while inserting records in the uploadTables"
            );
        }
    }

}
?>
