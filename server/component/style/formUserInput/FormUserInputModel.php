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
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
    }

    /* Private Methods ********************************************************/

    private function get_form_id(){        
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
        $this->db->begin_transaction();
        $res = $this->db->insert("user_input", array(
            "id_users" => $id_users,
            "id_sections" => $id,
            "id_section_form" => $this->get_form_id(),
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
        $this->db->commit();
        return $res;
    }

    /**
     * Update a form field entry in the database.
     *
     * @param int $id
     *  The id of the form field.
     * @param string $value
     *  The value of the form field.
     * @param string $id_record
     *  The id of user input record. This serves to group a set of input data
     * @retval int
     *  The number of affected rows or false if an error ocurred.
     */
    private function update_entry($id, $value)
    {

        $sql = "UPDATE user_input SET `value` = :value
            WHERE id_users = :id_users AND id_sections = :id_sections AND id_section_form = :id_section_form
            ORDER BY id DESC LIMIT 1;";
        return $this->db->execute_update_db($sql, array(
            "value" => $value,
            "id_users" => intval($_SESSION['id_user']),
            "id_sections" => $id,
            "id_section_form" => $this->get_form_id()
        ));
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
        $this->db->begin_transaction();
        $own_entries_only = $this->get_db_field("own_entries_only", "1");
        $entry_record = $this->fetch_entry_record($this->get_form_id(), $record_id, $own_entries_only);
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
                    "id_section_form" => $this->get_form_id(),
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
        $this->db->commit();
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
        $sql = "SELECT sft.content
            FROM sections_fields_translation AS sft
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            WHERE f.name = 'label' AND sft.id_sections = :id";
        $label = $this->db->query_db_first($sql,
            array(":id" => $id_section));
        if($label) return $label["content"];
        return "";
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
        $sql = "SELECT st.name FROM styles AS st
            LEFT JOIN sections AS s ON s.id_styles = st.id
            WHERE s.id = :id";
        $style = $this->db->query_db_first($sql,
            array(":id" => $id_section));
        if($style) return $style["name"];
        return "";
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
        $type = $this->db->query_db_first($sql,
            array(":id" => $id_section));
        if($type) return $type["content"];
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
        $sql = "SELECT * FROM user_input
            WHERE id_sections = :id AND id_section_form = :fid
            AND id_users = :uid";
        $res = $this->db->query_db($sql, array(
            ":id" => $id,
            ":fid" => $this->get_form_id(),
            ":uid" => $_SESSION['id_user'],
        ));
        if($res) return true;
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
        $sql = "SELECT * FROM user_input
            WHERE id_section_form = :fid AND id_users = :uid";
        $res = $this->db->query_db($sql, array(
            ":fid" => $this->get_form_id(),
            ":uid" => $_SESSION['id_user'],
        ));
        if($res) return true;
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
     * @retval int
     *  The number of affected rows in the database or false if an error
     *  ocurred.
     */
    public function save_user_input($user_input)
    {
        $count = 0;
        $id_record = null;
        if($this->is_log() || !$this->has_form_data()) {
            $id_record = $this->db->insert("user_input_record", array());
        }
        foreach($user_input as $id => $value)
        {
            if($this->is_log() || !$this->has_field_data($id))
                $res = $this->insert_new_entry($id, $value, $id_record, intval($_SESSION['id_user']));
            else
            {                
                $res = $this->update_entry($id, $value);
            }

            if($res === false)
                return false;
            else
                $count += $res;
        }
        // Once data is entered to the uiser input database the attributes in
        // the user_input service needs to be updated.
        $this->user_input->set_field_attrs();
        return $count;
    }

    /**
     * Update the user input to the database.
     *
     * @param array $user_input
     *  The array of input key => value pairs where the key is the name of the
     *  input field.
     * @param int $record_id
     * The record id
     * @retval int
     *  The number of affected rows in the database or false if an error
     *  ocurred.
     */
    public function update_user_input($user_input, $record_id)
    {
        $count = 0;
        foreach ($user_input as $id => $value) {
            $res = $this->update_entry_with_record_id($id, $value, $record_id);
            if ($res === false) {                
                return false;
            } else {
                $count += $res;
            }
        }
        // Once data is entered to the uiser input database the attributes in
        // the user_input service needs to be updated.
        $this->user_input->set_field_attrs();
        return $count;
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
        if($entry_data){
            // $entry_data = json_decode($entry_data, true);            
            $body = $this->get_entry_value($entry_data, $body);
            $subject = $this->get_entry_value($entry_data, $subject);
            $data_config = $this->get_entry_value($entry_data, $data_config);
            $email_address = $this->get_entry_value($entry_data, $email_address);
        }
        if ($data_config) {
            $fields = $this->retrieve_data($data_config);
            if ($fields) {
                foreach ($fields as $field_name => $field_value) {
                    $subject = str_replace($field_name, $field_value, $subject);
                    $body = str_replace($field_name, $field_value, $body);
                }
            }
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
    public function get_entry_record($form_name, $record_id, $own_entries_only){
        $form_id = $this->db->get_form_id($form_name);
        return $this->fetch_entry_record($form_id, $record_id, $own_entries_only);
    }

    /**
     * Mark this user input as removed in the database.
     *
     * @param int $record_id
     *  The record_id of the fields to be marked as removed.
     */
    public function delete_user_input($record_id){        
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
    public function get_form_field_name($field_id){
        $sql = "SELECT *
                FROM sections s
                INNER JOIN sections_fields_translation sft ON (s.id = sft.id_sections)
                WHERE sft.id_fields = 57 AND id = :id";
        $res = $this->db->query_db_first($sql, array(
            ":id" => $field_id,
        ));
        if($res) return $res['content'];
        else return false;
    }

    public function set_entry_data($entry_data){
        $this->entry_data = $entry_data;
    }
}
?>
