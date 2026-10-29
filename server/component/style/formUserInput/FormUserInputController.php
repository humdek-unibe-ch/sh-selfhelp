<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseController.php";
require_once __DIR__ . "/../../../service/ext/Gump.php";
/**
 * The controller class of formUserInput style component.
 */
class FormUserInputController extends BaseController
{
    /**
     * DB field 'alert_success' (empty string).
     * Shown after a successful insert, update, or delete when non-empty.
     */
    public $alert_success;

    /**
     * DB field 'alert_fail' (empty string).
     * Shown after a failed insert, update, or delete when non-empty.
     */
    public $alert_fail;

    /**
     * @param object $model
     * @param bool $auto_execute
     *  When false, subclasses wire BaseController then call execute() themselves
     *  (avoids fragile BaseController::__construct skips).
     */
    public function __construct($model, $auto_execute = true)
    {
        parent::__construct($model);
        if ($auto_execute) {
            $this->execute();
        }
    }

    /**
     * Use GUMP service to validate and sanitize user inputs.
     * Protected so subclasses (e.g. formUserInputLogList) can reuse it.
     *
     * @param object $gump
     * @return mixed
     *  false on validation error; else sanitized $key => $value map.
     */
    protected function check_user_input($gump)
    {
        $validation_rules = array();
        $filter_rules = array();
        $field_names = array();
        $post = array();
        foreach ($_POST as $name => $values) {
            $type = ''; // reset type for each field
            if (!isset($values['id'])) {
                $post[$name] = $values;
                continue;
            }
            $id_section = intval($values['id']);
            if (!isset($values['value'])) {
                $values['value'] = "";
            }
            $value = $values['value'];
            $label = $this->model->get_field_label($id_section);
            if ($label == "") {
                $label = $name;
            }
            $field_names[$name] = $label;
            $style = $this->model->get_field_style($id_section);
            if ($style == "slider") {
                $validation_rules[$name] = "integer";
                $filter_rules[$name] = "sanitize_numbers";
            } else if ($style == "textarea") {
                $filter_rules[$name] = "sanitize_string";
            } else if ($style == "select" || $style == "radio") {
                $filter_rules[$name] = "trim|sanitize_string";
            } else if ($style == "input") {
                $type = $this->model->get_field_type($id_section);
                if (
                    $type == "text" || $type == "checkbox" || $type == "month" || $type == "time" || $type == "datetime-local" || $type == "datetime"
                    || $type == "week" || $type == "search" || $type == "tel" || $type == "date"
                ) {
                    $filter_rules[$name] = "trim|sanitize_string";
                } else if ($type == "color") {
                    $validation_rules[$name] = "regex,/#[a-fA-F0-9]{6}/";
                } else if ($type == "email") {
                    $validation_rules[$name] = "valid_email";
                } else if ($type == "number" || $type == "range") {
                    $validation_rules[$name] = "numeric";
                } else if ($type == "url") {
                    $validation_rules[$name] = "valid_url";
                } else {
                    $filter_rules[$name] = "sanitize_string";
                }
            } else {
                $filter_rules[$name] = "sanitize_string";
            }
            if (is_array($value)) {
                $post[$name] = json_encode($value);
            } else {
                $post[$name] = $value;
                if ($type == "anonymous-holder") {
                    $post[$name] = str_repeat('*', strlen($value));
                }
            }
        }
        $gump->validation_rules($validation_rules);
        $gump->filter_rules($filter_rules);
        $gump->set_field_names($field_names);
        return $gump->run($post);
    }

    /**
     * Normalize a POST id that may arrive as scalar or ['value' => ...].
     *
     * @param mixed $value
     * @return int
     */
    protected function normalize_post_id($value)
    {
        if (is_array($value) && isset($value['value'])) {
            return intval($value['value']);
        }
        return intval($value);
    }

    /**
     * Authorize update/delete before mutating. Override for POST-only auth.
     *
     * @param int $selected_record_id
     * @param int $delete_record_id
     * @return bool
     */
    protected function authorize_write($selected_record_id, $delete_record_id)
    {
        if ($delete_record_id > 0) {
            return $this->has_access_delete($delete_record_id);
        }
        if ($selected_record_id > 0) {
            return $this->has_access_selected($selected_record_id);
        }
        return true;
    }

    /**
     * @param int $record_id
     * @return bool
     */
    protected function has_access_selected($record_id)
    {
        if ($record_id <= 0) {
            return true;
        }
        $entry_record = $this->model->get_form_entry_record(
            $record_id,
            $this->model->get_db_field("own_entries_only", 1)
        );
        return !empty($entry_record);
    }

    /**
     * @param int $delete_record_id
     * @return bool
     */
    protected function has_access_delete($delete_record_id)
    {
        if ($delete_record_id <= 0) {
            return true;
        }
        $entry_record = $this->model->get_form_entry_record(
            $delete_record_id,
            $this->model->get_db_field("own_entries_only", 1)
        );
        return !empty($entry_record);
    }

    /**
     * Push success message from alert_success (insert / update / delete).
     */
    protected function push_success_alert()
    {
        if ($this->alert_success !== "" && $this->alert_success !== null) {
            $this->success_msgs[] = $this->alert_success;
        }
    }

    /**
     * Push fail message from alert_fail, or a fallback string.
     *
     * @param string $fallback
     */
    protected function push_fail_alert($fallback)
    {
        if ($this->alert_fail !== "" && $this->alert_fail !== null) {
            $this->error_msgs[] = $this->alert_fail;
        } else if ($fallback !== "") {
            $this->error_msgs[] = $fallback;
        }
    }

    /**
     * Hook after a successful write (subclass: redirect to list, etc.).
     */
    protected function after_successful_write()
    {
    }

    /**
     * Trigger type stored on the row / passed to formActions for this write.
     * Forms only use finished here (deleted goes through UserInput::delete_data);
     * started/updated are survey triggers and are never queued for forms.
     *
     * @param int $selected_record_id
     * @param int $delete_record_id
     * @return string
     */
    protected function get_write_trigger_type($selected_record_id, $delete_record_id)
    {
        return actionTriggerTypes_finished;
    }

    /**
     * Single hook for the write payload + trigger + decode flag.
     * Called once from execute() after authorize_write. Subclasses that supply
     * a prepared field map (e.g. CMS quick actions) override this instead of
     * intercepting check_user_input / get_write_trigger_type separately.
     *
     * @param object $gump
     * @param int $selected_record_id
     * @param int $delete_record_id
     * @return array{fields:mixed,trigger_type:string,decode:bool}
     *  fields: false on GUMP failure, else field => value map (no trigger_type yet)
     *  decode: when true, execute() runs the html_entity_decode / json round-trip
     */
    protected function prepare_write_data($gump, $selected_record_id, $delete_record_id)
    {
        return array(
            'fields' => $this->check_user_input($gump),
            'trigger_type' => $this->get_write_trigger_type(
                $selected_record_id,
                $delete_record_id
            ),
            'decode' => true,
        );
    }

    public function execute()
    {
        if (count($_POST) === 0) {
            // Forms only fire formActions on finished (submit) and deleted, never on page view.
            return;
        }
        if (
            !isset($_POST['__form_name'])
            || $_POST['__form_name'] !== $this->model->get_db_field("name")
        ) {
            return;
        }
        unset($_POST['__form_name']);

        if (isset($_POST[ENTRY_RECORD_ID]) && isset($_POST[ENTRY_RECORD_ID]['value'])) {
            $_POST[ENTRY_RECORD_ID] = $_POST[ENTRY_RECORD_ID]['value'];
        }
        if (isset($_POST[SELECTED_RECORD_ID])) {
            $_POST[SELECTED_RECORD_ID] = $this->normalize_post_id($_POST[SELECTED_RECORD_ID]);
        }
        if (isset($_POST[DELETE_RECORD_ID])) {
            $_POST[DELETE_RECORD_ID] = $this->normalize_post_id($_POST[DELETE_RECORD_ID]);
        }

        $selected_record_id = isset($_POST[SELECTED_RECORD_ID]) ? intval($_POST[SELECTED_RECORD_ID]) : 0;
        $delete_record_id = isset($_POST[DELETE_RECORD_ID]) ? intval($_POST[DELETE_RECORD_ID]) : 0;

        if (!$this->authorize_write($selected_record_id, $delete_record_id)) {
            $this->fail = true;
            $this->alert_fail = $this->model->get_db_field("alert_fail", "");
            if ($delete_record_id > 0) {
                $this->push_fail_alert("The record was not deleted");
            } else {
                $this->push_fail_alert("An unexpected problem occurred. Please Contact the Server Administrator");
            }
            return;
        }

        $this->alert_success = $this->model->get_db_field("alert_success");
        $this->alert_fail = $this->model->get_db_field("alert_fail", "");
        $gump = new GUMP('de');
        $write = $this->prepare_write_data($gump, $selected_record_id, $delete_record_id);
        $user_input = isset($write['fields']) ? $write['fields'] : false;
        if (is_array($user_input) && !empty($write['decode'])) {
            array_walk_recursive($user_input, function (&$v) {
                $v = html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            });
            $user_input = json_decode(html_entity_decode(json_encode($user_input), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
        }
        if (!is_array($user_input)) {
            $user_input = false;
        } else {
            $user_input['trigger_type'] = isset($write['trigger_type'])
                ? $write['trigger_type']
                : $this->get_write_trigger_type($selected_record_id, $delete_record_id);
        }
        if (is_array($user_input)) {
            unset($user_input[DELETE_RECORD_ID]);
            unset($user_input[SELECTED_RECORD_ID]);
        }
        foreach ($_POST as $key => $value) {
            if (isset($_POST[$key]['id'])) {
                $_POST[$key] = isset($value['value']) ? $value['value'] : '';
            }
        }

        if ($user_input === false) {
            $this->fail = true;
            if (isset($_POST['mobile']) && $_POST['mobile']) {
                foreach ($gump->get_errors_array(true) as $key => $err) {
                    $this->error_msgs[] = $err;
                }
            } else {
                $this->error_msgs = $gump->get_errors_array(true);
            }
            if ($this->alert_fail !== "" && $this->alert_fail !== null) {
                $this->error_msgs[] = $this->alert_fail;
            }
        } else if ($delete_record_id > 0) {
            $res = $this->model->delete_user_input($delete_record_id);
            if ($res === false) {
                $this->fail = true;
                $this->push_fail_alert("The record was not deleted");
            } else {
                $this->success = true;
                $this->push_success_alert();
                $this->after_successful_write();
            }
        } else {
            $record_id = $selected_record_id > 0
                ? $this->model->update_user_input($user_input, $selected_record_id)
                : $this->model->save_user_input($user_input);
            if ($record_id === false) {
                $this->fail = true;
                $this->push_fail_alert("An unexpected problem occurred. Please Contact the Server Administrator");
            } else if ($record_id > 0) {
                $this->success = true;
                $this->push_success_alert();
                $this->model->reload_children();
                $this->after_successful_write();
            }
        }

        $redirect_at_end = $this->model->get_db_field("redirect_at_end", "");
        if (!(isset($_POST['mobile']) && $_POST['mobile']) && $redirect_at_end != "" && !isset($_POST[ENTRY_RECORD_ID])) {
            $redirect_at_end = $this->model->get_services()->get_router()->get_url($redirect_at_end);
            header("Location: " . $redirect_at_end);
            die();
        }
    }
}
?>
