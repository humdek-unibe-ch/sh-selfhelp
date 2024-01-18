<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseController.php";
require_once SERVICE_PATH . "/ext/Gump.php";
/**
 * The controller class of formUserInput style component.
 */
class FormUserInputController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'alert_success' (empty string).
     * The allert message to be shown if the content was updated successfully.
     */
    public $alert_success;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);        
    }

    /* Private Methods ********************************************************/

    /**
     * Use GUMP service to validate and sanitize user inputs.
     *
     * @retval mixed
     *  If a validation error occurred, false is returned.
     *  If no validation error occured, a $key => $value array is returend where
     *  $key is the name of the field and $value the sanitized user input.
     */
    private function check_user_input($gump)
    {
        $validation_rules = array();
        $filter_rules = array();
        $field_names = array();
        $post = array();
        foreach($_POST as $name => $values)
        {
            if(!isset($values['id'])) continue;
            $id_section = intval($values['id']);
            if(!isset($values['value']))
                $values['value'] = "";
            $value = $values['value'];
            $label = $this->model->get_field_label($id_section);
            if($label == "")
                $label = $name;
            $field_names[$id_section] = $label;
            // determine the type of the field
            $style = $this->model->get_field_style($id_section);
            if($style == "slider")
            {
                $validation_rules[$id_section] = "integer";
                $filter_rules[$id_section] = "sanitize_numbers";
            }
            else if($style == "textarea")
                $filter_rules[$id_section] = "sanitize_string";
            else if($style == "select" || $style == "radio")
                $filter_rules[$id_section] = "trim|sanitize_string";
            else if ($style == "input") {
                $type = $this->model->get_field_type($id_section);
                if (
                    $type == "text" || $type == "checkbox" || $type == "month" || $type == "time" || $type == "datetime-local" || $type == "datetime"
                    || $type == "week" || $type == "search" || $type == "tel" || $type == "date"
                )
                    $filter_rules[$id_section] = "trim|sanitize_string";
                else if ($type == "color")
                    $validation_rules[$id_section] = "regex,/#[a-fA-F0-9]{6}/";
                // else if($type == "date")
                //     $validation_rules[$id_section] = "date";
                else if ($type == "email")
                    $validation_rules[$id_section] = "valid_email";
                else if ($type == "number" || $type == "range")
                    $validation_rules[$id_section] = "numeric";
                // else if($type == "time")
                //    $validation_rules[$id_section] = "regex,/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/";
                else if ($type == "url")
                    $validation_rules[$id_section] = "valid_url";
                else
                    $filter_rules[$id_section] = "sanitize_string";
            } else
                $filter_rules[$id_section] = "sanitize_string";
            if (is_array($value)) {
                $post[$id_section] = json_encode($value); // save the data as json
            } else {
                $post[$id_section] = $value;
            }
        }
        $gump->validation_rules($validation_rules);
        $gump->filter_rules($filter_rules);
        $gump->set_field_names($field_names);
        return $gump->run($post);
    }

    /**
     * Check if entry is a record and check if the user has access to delete it or update it
     * @retval boolean
     * true if it is ok and false if no access
     */
    private function has_access()
    {
        if ($this->model->get_selected_record_id() > 0) {
            $entry_record = $this->model->get_form_entry_record($this->model->get_db_field("name"), $this->model->get_selected_record_id(), $this->model->get_db_field("own_entries_only", 1));
            if ($entry_record) {
                return true;
            } else {
                // no access
                return false;
            }
        } else {
            // not a specific case
            return true;
        }
    }

    public function execute(){   
        if(count($_POST) === 0){
            $this->model->queue_job_from_actions(actionTriggerTypes_started);
            return;
        } 
        if(!isset($_POST['__form_name'])
            || $_POST['__form_name'] !== $this->model->get_db_field("name"))
            return;
        unset($_POST['__form_name']);
        if (isset($_POST[ENTRY_RECORD_ID]) && isset($_POST[ENTRY_RECORD_ID]['value'])) {
            $_POST[ENTRY_RECORD_ID] = $_POST[ENTRY_RECORD_ID]['value']; // normalize the variable when it comes from mobile call
        }
        if (isset($_POST[SELECTED_RECORD_ID]) && isset($_POST[SELECTED_RECORD_ID]['value'])) {
            $_POST[SELECTED_RECORD_ID] = $_POST[SELECTED_RECORD_ID]['value']; // normalize the variable when it comes from mobile call
        }
        if (isset($_POST[DELETE_RECORD_ID]) && isset($_POST[DELETE_RECORD_ID]['value'])) {
            $_POST[DELETE_RECORD_ID] = $_POST[DELETE_RECORD_ID]['value']; // normalize the variable when it comes from mobile call
        }

        if(!$this->has_access()){
            // if the user has no acess to edit or delete this record abort and return
            return;
        }

        $this->alert_success = $this->model->get_db_field("alert_success");
        $gump = new GUMP('de');
        $user_input = $this->check_user_input($gump);
        if ($user_input === false) {
            $this->fail = true;
            if (isset($_POST['mobile']) && $_POST['mobile']) {
                foreach ($gump->get_errors_array(true) as $key => $err) {
                    $this->error_msgs[] = $err;
                }                
            } else {
                $this->error_msgs = $gump->get_errors_array(true);
            }
        }else if(isset($_POST[DELETE_RECORD_ID])){
            $res =  $this->model->delete_user_input($_POST[DELETE_RECORD_ID]);
            if ($res === false) {
                $this->fail = true;
                $this->error_msgs[] = "The record was not deleted";
            } else {
                $this->success = true;
                $this->alert_success = "The record: " . $_POST[DELETE_RECORD_ID] . " was deleted.";
                if ($this->alert_success !== "")
                    $this->success_msgs[] = "The record: " . $_POST[DELETE_RECORD_ID] . " was deleted.";
            }
        }
        else
        {            
            $record_id = isset($_POST[SELECTED_RECORD_ID]) ? $this->model->update_user_input($user_input, $_POST[SELECTED_RECORD_ID]) : $this->model->save_user_input($user_input);
            if($record_id === false)
            {
                $this->fail = true;
                $this->error_msgs[] = "An unexpected problem occurred. Please Contact the Server Administrator";
            }
            else if($record_id > 0)
            {
                if(isset($_POST['btnSubmitAndSend']) && $_POST['btnSubmitAndSend'] == 'send_email'){
                    $this->model->send_feedback_email();
                }
                $this->success = true;
                if($this->alert_success !== "")
                    $this->success_msgs[] = $this->alert_success;
                $this->model->queue_job_from_actions(actionTriggerTypes_finished, $record_id);
                $this->model->reload_children();
            }
        }
        $redirect = $this->model->get_db_field("redirect_at_end", "");
        if(!(isset($_POST['mobile']) && $_POST['mobile']) && $record_id && $redirect != "" && !isset($_POST[ENTRY_RECORD_ID])){
            //$redirect = str_replace("/", "", $redirect);
            $redirect_url = $this->model->get_services()->get_router()->get_url(str_replace("/", "", $redirect));
            header("Location: " . ($redirect_url != '' ? $redirect_url : $redirect));
            die();
        }
    }
}
?>
