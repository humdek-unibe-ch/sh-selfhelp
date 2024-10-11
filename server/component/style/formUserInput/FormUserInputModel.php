<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/../StyleComponent.php";

/**
 * This class is used to prepare all data related to the form style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class FormUserInputModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * Entry data if the style is used in entry visualization
     */
    private $entry_data = null;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {        
        parent::__construct($services, $id, $params, $id_page, $entry_record);
        if(!$this->is_log()){
            // if not log load data if exists
            $this->reload_children();
        }
        
    }

    /* Private Methods ********************************************************/

    /**
     * Get form id
     * @return int
     * Return the form id
     */
    private function get_form_id()
    {
        return $this->get_db_field("id");
    }        

    /**
     * Formats the section ID as a zero-padded string.
     *
     * This method formats the `section_id` property of the class as a zero-padded string
     * with a length of 10 characters. It is typically used to generate a consistent table name
     * or identifier format.
     *
     * @return string A zero-padded string representation of the section ID, 10 characters in length.
     */
    private function get_table_name_from_form_id(){
        return sprintf('%010d', $this->section_id);
    }

    /**
     * Insert a new form field entry to the database.
     *
     * @param int $id
     *  The id of the form field.
     * @param string $value
     *  The value of the form field.
     * @param string $id_record
     *  The id of user input record. This serves to group a set of input data
     * @param int $id_users
     * The user who create the record
     *  fields.
     * @retval int
     *  The number of affected rows or false if an error ocurred.
     */
    private function insert_new_entry($id, $value, $id_record, $id_users)
    {
        $res = $this->db->insert("user_input", array(
            "id_users" => $id_users,
            "id_sections" => $id,
            "value" => $value,
            "id_user_input_record" => $id_record,
        ));
        $this->transaction->add_transaction(
            transactionTypes_insert,
            transactionBy_by_user,
            $_SESSION['id_user'],
            $this->transaction::TABLE_USER_INPUT,
            $id_record
        );
        return $res;
    }

    /* Public Methods *********************************************************/

    /**
     * Fetch the label of a form field from the database if available.
     *
     * @param intval $id_section
     *  The section id of the form field from which the label will be fetched.
     * @retval string
     *  The label of the form field or the empty string if the label is not
     *  available.
     */
    public function get_field_label($id_section)
    {
        return $this->user_input->get_field_label($id_section);
    }

    /**
     * Fetch the style of a form field from the database if available.
     *
     * @param intval $id_section
     *  The section id of the form field from which the style will be fetched.
     * @retval string
     *  The style of the form field or the empty string if the style is not
     *  available.
     */
    public function get_field_style($id_section)
    {
        return $this->user_input->get_field_style($id_section);
    }

    /**
     * Fetch the type of a form field from the database if available.
     *
     * @param intval $id_section
     *  The section id of the form field from which the type will be fetched.
     * @retval string
     *  The type of the form field or the empty string if the type is not
     *  available.
     */
    public function get_field_type($id_section)
    {
        $sql = "SELECT sft.content
            FROM sections_fields_translation AS sft
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            WHERE f.name = 'type_input' AND sft.id_sections = :id";
        $type = $this->db->query_db_first(
            $sql,
            array(":id" => $id_section)
        );
        if ($type) return $type["content"];
        return "";
    }

    /**
     * Check the last record_id for the form. Used for the update form which is not is_log
     * @retval int
     *  return record_id, if not return false
     */
    public function get_id_record()
    {
        $own_entries_only = $this->get_db_field("own_entries_only", "1");
        $res = $this->user_input->get_data($this->get_form_id(),'ORDER BY record_id DESC',$own_entries_only, null, true);
        if ($res) return $res['record_id'];
        else return false;
    }

    /**
     * Checks whether the form is a logging or a documentation form.
     *
     * @retval bool
     *  True if the form is a log form, false otherwise.
     */
    public function is_log()
    {
        return $this->get_db_field("is_log", false);
    }

    /**
     * Save the user input to the database.
     *
     * @param array $user_input
     *  The array of input key => value pairs where the key is the name of the
     *  input field.
     * @return int|false
     *  Return the record if or false on error
     */
    public function save_user_input($user_input)
    {        
        $res = $this->user_input->save_data(
            transactionBy_by_user,
            $this->get_table_name_from_form_id(),
            (array)$user_input
        );    
        if(!$this->is_log()){
            // if not log load data if exists
            $this->reload_children();
        }
        return $res;
    }

    /**
     * Update the user input to the database.
     *
     * @param array $user_input
     *  The array of input key => value pairs where the key is the name of the
     *  input field.
     * @param int $record_id
     * The record id
     * @return int|false
     * Return the updated record id or false on error
     */
    public function update_user_input($user_input, $record_id)
    {
        $res = $this->user_input->save_data(
            transactionBy_by_user,
            $this->get_table_name_from_form_id(),
            (array)$user_input,
            array(
                "record_id" => $record_id
            )
        );        
        return $res;
    }

    /**
     * the name of the form
     * @param int $record_id
     * the record id
     * * @param int $own_entries_only
     * If true it loads only records created by the same user
     * @retval @array
     * the record row
     */
    public function get_form_entry_record($record_id, $own_entries_only)
    {
        $form_id = $this->user_input->get_dataTable_id($this->get_table_name_from_form_id());
        $filter = " AND record_id = " . $record_id;
        if (!$form_id) {
            return false;
        }
        return $this->user_input->get_data($form_id, $filter, $own_entries_only);
    }

    /**
     * Mark this user input as removed in the database.
     *
     * @param int $record_id
     *  The record_id of the fields to be marked as removed.
     */
    public function delete_user_input($record_id)
    {
        $this->db->begin_transaction();
        $res = $this->db->update_by_ids('user_input', array('removed' => 1), array('id_user_input_record' => $record_id));
        $this->queue_job_from_actions(actionTriggerTypes_deleted, $record_id);
        $this->transaction->add_transaction(
            transactionTypes_delete,
            transactionBy_by_user,
            $_SESSION['id_user'],
            $this->transaction::TABLE_USER_INPUT,
            $record_id
        );
        $this->db->commit();
        return $res;
    }

    /**
     * Get the form field id
     * @param int $field_id
     * the section_id of the field
     * @retval string the fiedl name
     */
    public function get_form_field_name($field_id)
    {
        return $this->user_input->get_form_field_name($field_id);
    }

    public function set_entry_data($entry_data)
    {
        $this->entry_data = $entry_data;
    }   

    /**
     * Prepare the form data and call the queue_job_from_actions
     * @param string $trigger_type
     * Form started or finished
     */
    public function queue_job_from_actions($trigger_type, $record_id = null)
    {
        $form_data = array(
            "trigger_type" => $trigger_type,
            "form_name" => $this->get_db_field("name"),
            "form_id" => $this->section_id,
            "form_fields" => ($record_id ? array_merge($_POST, array('record_id' => $record_id)) : $_POST)
        );
        $this->user_input->queue_job_from_actions($form_data);
    }

    public function reload_children()
    {
        $form_id = $this->user_input->get_dataTable_id($this->get_table_name_from_form_id());
        if ($form_id && !$this->is_log()) {
            $data = $this->user_input->get_data_for_user($form_id, $_SESSION['id_user'], '', true);
            $this->set_entry_record($data);
        }
        $this->loadChildren();
    }

    /**
     * Get the selected record if there is one from entryRecord or entryList
     * @return int
     * Record id or -1;
     */
    public function get_selected_record_id()
    {
        $entry_record = $this->get_entry_record();
        if ($entry_record) {
            return isset($entry_record[ENTRY_RECORD_ID]) ? $entry_record[ENTRY_RECORD_ID] : -1;
        }
        return -1;
    }
}
?>
