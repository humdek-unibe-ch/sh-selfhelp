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

    /**
     * Update a form field entry in the database.
     *
     * @param int $id
     *  The id of the form field.
     * @param string $value
     *  The value of the form field.
     * @param int $record_id
     * The id_user_input_record from the table user_input
     * @retval int
     *  The number of affected rows or false if an error ocurred.
     */
    private function update_entry_with_record_id($id, $value, $record_id)
    {        
        $own_entries_only = $this->get_db_field("own_entries_only", "1");
        $filter = " AND deleted = 0 AND record_id = " . $record_id;
        $entry_record = $this->user_input->get_data($this->get_form_id(), $filter, $own_entries_only, FORM_INTERNAL, null, true);
        $field_name = $this->get_form_field_name($id);
        $res = false;
        $tran_type = '';
        if (isset($entry_record[$field_name])) {
            // field exists update it
            $res = $this->db->update_by_ids(
                "user_input",
                array(
                    "value" => $value,
                ),
                array(
                    "id_sections" => $id,
                    "id_user_input_record" => $record_id
                )
            );
            $tran_type = transactionTypes_update;
        } else {
            // the field is new and does not exist
            // insert it
            // insert it with user_id of the creator - otherwise the row cannot be grouped
            // add transaction
            $res = $this->insert_new_entry($id, $value, $record_id, $entry_record['user_id']);
            $tran_type = transactionTypes_insert;
        }
        $this->transaction->add_transaction(
            $tran_type,
            transactionBy_by_user,
            $_SESSION['id_user'],
            $this->transaction::TABLE_USER_INPUT,
            $record_id
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
        $this->user_input->get_field_style($id_section);
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
     * Check whether user has already submitted data to this form field.
     *
     * @param int $id
     *  The section id of the field to check for.
     * @retval bool
     *  True if data exists, false otherwise.
     */
    public function has_field_data($id)
    {
        $res = $this->user_input->get_input_fields(array(
            "id_section" => $id,
            "id_user" => $_SESSION['id_user'],
            "form_id" => $this->get_form_id()
        ));
        if ($res) return true;
        else return false;
    }

    /**
     * Check whether user has already submitted data to this form.
     *
     * @retval bool
     *  True if data exists, false otherwise.
     */
    public function has_form_data()
    {
        $res = $this->user_input->get_input_fields(array(
            "id_user" => $_SESSION['id_user'],
            "form_id" => $this->get_form_id()
        ));
        if ($res) return true;
        else return false;
    }

    /**
     * Check the last record_id for the form. Used for the update form which is not is_log
     * @retval int
     *  return record_id, if not return false
     */
    public function get_id_record()
    {
        $own_entries_only = $this->get_db_field("own_entries_only", "1");
        $res = $this->user_input->get_data($this->get_form_id(),'ORDER BY record_id DESC',$own_entries_only, FORM_INTERNAL, null, true);
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
        $id_record = null;
        if ($this->is_log() || !$this->has_form_data()) {
            $id_record = $this->db->insert("user_input_record", array("id_sections" => $this->get_form_id()));
        }
        $this->db->begin_transaction();
        foreach ($user_input as $id => $value) {
            if ($this->is_log() || !$this->has_field_data($id))
                $res = $this->insert_new_entry($id, $value, $id_record, intval($_SESSION['id_user']));
            else {
                if ($id_record == null) {
                    $id_record = $this->get_id_record();
                }
                $res = $this->update_entry_with_record_id($id, $value, $id_record);
            }

            if ($res === false)
                return false;
        }
        $this->db->commit();
        $this->db->get_cache()->clear_cache($this->db->get_cache()::CACHE_TYPE_USER_INPUT); // clear the cache we did changes
        // Once data is entered to the uiser input database the attributes in
        // the user_input service needs to be updated.
        $this->user_input->set_field_attrs();
        return $id_record;
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
        foreach ($user_input as $id => $value) {
            $res = $this->update_entry_with_record_id($id, $value, $record_id);
            if ($res === false) {
                return false;
            }
        }
        // Once data is entered to the uiser input database the attributes in
        // the user_input service needs to be updated.
        $this->db->get_cache()->clear_cache($this->db->get_cache()::CACHE_TYPE_USER_INPUT); // clear the cache we did changes
        $this->user_input->set_field_attrs();
        return $record_id;
    }

    /**
     * Send feedback email to the user after the data is saved.
     * If there is data_config we retreieve the data base don the config
     */
    public function send_feedback_email()
    {
        $entry_data = $this->entry_data;
        $data_config = $this->get_db_field("data_config", '');
        $subject = $this->get_db_field("email_subject", '');
        $body = $this->get_db_field("email_body", '');
        $email_address = $this->get_db_field("email_address", '');
        if ($entry_data) {
            // $entry_data = json_decode($entry_data, true);            
            $body = $this->get_entry_value($entry_data, $body);
            $subject = $this->get_entry_value($entry_data, $subject);
            $data_config = $this->get_entry_value($entry_data, $data_config);
            $email_address = $this->get_entry_value($entry_data, $email_address);
        }
        $email_address = str_replace('@email_user', $this->db->select_by_uid('users', $_SESSION['id_user'])['email'], $email_address);
        $mail = array(
            "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
            "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
            "date_to_be_executed" => date('Y-m-d H:i:s', time()),
            "from_email" => PROJECT_NAME . '@unibe.ch',
            "from_name" => PROJECT_NAME,
            "reply_to" => PROJECT_NAME . '@unibe.ch',
            "recipient_emails" => $email_address,
            "subject" => $subject,
            "body" => $body,
            "description" => "FormUserInput Feedback email"
        );
        $mail['id_users'][] = $_SESSION['id_user'];
        $this->job_scheduler->add_and_execute_job($mail, transactionBy_by_user);
    }

    /**
     * Get form user input record row
     * @param string $form_name
     * the name of the form
     * @param int $record_id
     * the record id
     * * @param int $own_entries_only
     * If true it loads only records created by the same user
     * @retval @array
     * the record row
     */
    public function get_form_entry_record($form_name, $record_id, $own_entries_only)
    {
        $form_id = $this->user_input->get_form_id($form_name);
        $filter = " AND deleted = 0 AND record_id = " . $record_id;
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
            "form_type" => FORM_INTERNAL,
            "form_fields" => ($record_id ? array_merge($_POST, array('record_id' => $record_id)) : $_POST)
        );
        $this->user_input->queue_job_from_actions($form_data);
    }

    public function reload_children(){
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
