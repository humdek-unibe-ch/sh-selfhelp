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
            else if($style == "input")
            {
                $type = $this->model->get_field_type($id_section);
                if($type == "text" || $type == "checkbox" || $type == "month" || $type == "time" || $type == "datetime-local" || $type == "datetime"
                    || $type == "week" || $type == "search" || $type == "tel" || $type == "date")
                    $filter_rules[$id_section] = "trim|sanitize_string";
                else if($type == "color")
                    $validation_rules[$id_section] = "regex,/#[a-fA-F0-9]{6}/";
                // else if($type == "date")
                //     $validation_rules[$id_section] = "date";
                else if($type == "email")
                    $validation_rules[$id_section] = "valid_email";
                else if($type == "number" || $type == "range")
                    $validation_rules[$id_section] = "numeric";
                // else if($type == "time")
                //    $validation_rules[$id_section] = "regex,/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/";
                else if($type == "url")
                    $validation_rules[$id_section] = "valid_url";
                else
                    $filter_rules[$id_section] = "sanitize_string";
            }
            else
                $filter_rules[$id_section] = "sanitize_string";
            $post[$id_section] = $value;
        }
        $gump->validation_rules($validation_rules);
        $gump->filter_rules($filter_rules);
        $gump->set_field_names($field_names);
        return $gump->run($post);
    }

    public function execute(){        
        if(count($_POST) === 0) return;
        if(!isset($_POST['__form_name'])
            || $_POST['__form_name'] !== $this->model->get_db_field("name"))
            return;
        unset($_POST['__form_name']);
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
        }else if(isset($_POST['delete_record_id'])){
            $res =  $this->model->delete_user_input($_POST['delete_record_id']);
            if ($res === false) {
                $this->fail = true;
                $this->error_msgs[] = "The record was not deleted";
            } else {
                $this->success = true;
                $this->alert_success = "The record: " . $_POST['delete_record_id'] . " was deleted.";
                if ($this->alert_success !== "")
                    $this->success_msgs[] = "The record: " . $_POST['delete_record_id'] . " was deleted.";
            }
        }
        else
        {
            $res = isset($_POST['selected_record_id']) ? $this->model->update_user_input($user_input, $_POST['selected_record_id']) : $this->model->save_user_input($user_input);
            if($res === false)
            {
                $this->fail = true;
                $this->error_msgs[] = "An unexpected problem occurred. Please Contact the Server Administrator";
            }
            else if($res > 0)
            {
                if(isset($_POST['btnSubmitAndSend']) && $_POST['btnSubmitAndSend'] == 'send_email'){
                    $this->model->send_feedback_email();
                }
                $this->success = true;
                if($this->alert_success !== "")
                    $this->success_msgs[] = $this->alert_success;
            }
        }
    }
}
?>
