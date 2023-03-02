<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class ModuleFormsActionView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * action id, 
     * if it is > 0  edit/delete project page     
     */
    private $sid;

    /**
     * The mode type of the form EDIT, DELETE, INSERT, VIEW     
     */
    private $mode;

    /**
     * the current selcted action
     */
    private $action;

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller, $mode, $sid)
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
        $this->sid = $sid;
        $this->action = $this->model->get_services()->get_db()->select_by_uid("view_formActions", $this->sid);
        if ($this->action) {
            $this->action['schedule_info'] = json_decode($this->action['schedule_info'], true);
            if (isset($this->action['schedule_info']['config'])) {
                $this->action['schedule_info']['config'] = json_encode($this->action['schedule_info']['config'], JSON_PRETTY_PRINT);
            }
        }
    }

    /* Private Methods ********************************************************/

    /**
     * get time intervals from first to tenth
     *
     *  @retval array
     *  value int,
     *  text string
     */
    private function get_time_intervals_text()
    {
        $arr = array();
        array_push($arr, array("value" => 1, "text" => '1st'));
        array_push($arr, array("value" => 2, "text" => '2nd'));
        array_push($arr, array("value" => 3, "text" => '3rd'));
        foreach (range(4, 20) as $range) {
            array_push($arr, array("value" => $range, "text" => $range . 'th'));
        }
        return $arr;
    }

    /**
     * get notification card view.
     *
     *  @retval card
     */
    private function get_schedule_info_card_view()
    {
        return new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "id" => "schedule_info",
            "is_expanded" => true,
            "is_collapsible" => true,
            "title" => 'Schedule info',
            "children" => array(
                new BaseStyleComponent("select", array(
                    "label" => "Reminder for form",
                    "value" => isset($this->action['id_forms_reminder']) ? $this->action['id_forms_reminder'] : "",
                    "is_required" => true,
                    "name" => "id_forms_reminder",
                    "id" => "id_forms_reminder",
                    "css" => "d-none",
                    "items" => $this->model->get_forms(),
                    "disabled" => true
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Reminder for notification (Needed for form with sessions and multiple block schedules)",
                    "value" => isset($this->action['id_formActions']) ? $this->action['id_formActions'] : "",
                    "is_required" => false,
                    "name" => "id_formActions",
                    "id" => "id_formActions",
                    "css" => "d-none",
                    "items" => $this->model->get_notifications(),
                    "disabled" => true
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Type",
                    "is_required" => true,
                    "id" => "type",
                    "value" => isset($this->action["schedule_info"][notificationTypes]) ? $this->action["schedule_info"][notificationTypes] : '',
                    "name" => "schedule_info[notificationTypes]",
                    "items" => $this->model->get_services()->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => notificationTypes)),
                    "disabled" => true
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Valid after scheduled time (in minutes). [It is used for surveys with multiple sessions and reminders]",
                    "id" => "valid",
                    "locale" => "",
                    "css" => "d-none mt-3",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['valid']) ? $this->action["schedule_info"]['valid'] : ''
                    ))),
                )),
                new BaseStyleComponent("select", array(
                    "label" => "When",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"][actionScheduleTypes]) ? $this->action["schedule_info"][actionScheduleTypes] : '',
                    "name" => "schedule_info[actionScheduleTypes]",
                    "items" => $this->model->get_services()->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => actionScheduleTypes)),
                    "disabled" => true
                )),
                new BaseStyleComponent("template", array(
                    "path" => __DIR__ . "/tpl_datepicker.php",
                    "items" => array(
                        "name" => 'schedule_info[custom_time]',
                        "value" => isset($this->action["schedule_info"]['custom_time']) ? $this->action["schedule_info"]['custom_time'] : '',
                        "disabled" => "disabled",
                        "id" => "custom_time"
                    )
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Send After",
                    "id" => "send_after",
                    "locale" => "",
                    "css" => "send_after d-none",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['send_after']) ? $this->action["schedule_info"]['send_after'] : ''
                    ))),
                )),
                new BaseStyleComponent("select", array(
                    "id" => "send_after_type",
                    "css" => 'd-none',
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_after_type']) ? $this->action["schedule_info"]['send_after_type'] : '',
                    "name" => "schedule_info[send_after_type]",
                    "items" => $this->model->get_services()->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => timePeriod)),
                    "disabled" => true
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Send on",
                    "css" => 'd-none',
                    "id" => "send_on",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_on']) ? $this->action["schedule_info"]['send_on'] : '',
                    "name" => "schedule_info[send_on]",
                    "items" => $this->get_time_intervals_text(),
                    "disabled" => true
                )),
                new BaseStyleComponent("select", array(
                    "id" => "send_on_day",
                    "css" => 'd-none mb-3',
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_on_day']) ? $this->action["schedule_info"]['send_on_day'] : '',
                    "name" => "schedule_info[send_on_day]",
                    "items" => $this->model->get_services()->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => weekdays)),
                    "disabled" => true
                )),
                new BaseStyleComponent("template", array(
                    "path" => __DIR__ . "/tpl_timepicker.php",
                    "items" => array(
                        "name" => 'schedule_info[send_on_day_at]',
                        "label" => "At",
                        "id" => "send_on_day_at",
                        "value" => isset($this->action["schedule_info"]['send_on_day_at']) ? $this->action["schedule_info"]['send_on_day_at'] : '',
                        "disabled" => "disabled",
                    )
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Reminder for notification",
                    "is_required" => true,
                    "id" => "linked_action",
                    "value" => isset($this->action["schedule_info"]['linked_action']) ? $this->action["schedule_info"]['linked_action'] : '',
                    "name" => "schedule_info[linked_action]",
                    "items" => $this->model->get_services()->get_db()->fetch_table_as_select_values('formActions', 'id', array('name')),
                    "disabled" => true
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "From Email",
                    "id" => "from_email",
                    "locale" => "",
                    "css" => "mt-3",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['from_email']) ? $this->action["schedule_info"]['from_email'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "URL",
                    "locale" => "",
                    "id" => "url",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['url']) ? $this->action["schedule_info"]['url'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "From Name",
                    "locale" => "",
                    "id" => "from_name",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['from_name']) ? $this->action["schedule_info"]['from_name'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Reply To",
                    "locale" => "",
                    "id" => "reply_to",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['reply_to']) ? $this->action["schedule_info"]['reply_to'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "To",
                    "locale" => "",
                    "css" => "mt-3",
                    "id" => "to",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['recipient']) ? $this->action["schedule_info"]['recipient'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Subject",
                    "locale" => "",
                    "id" => "subject",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['subject']) ? $this->action["schedule_info"]['subject'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Body",
                    "locale" => "",
                    "id" => "body",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['body']) ? $this->action["schedule_info"]['body'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Attachments",
                    "locale" => "",
                    "id" => "attachments",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['attachments']) ? $this->action["schedule_info"]['attachments'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Config",
                    "locale" => "",
                    "id" => "config",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['config']) ? $this->action["schedule_info"]['config'] : ''
                    ))),
                ))
            )
        ));
    }

    /**
     * get notification card.
     *
     *  @retval card
     */
    private function get_schedule_info_card()
    {
        return new BaseStyleComponent("card", array(
            "css" => "mb-3 d-none",
            "is_expanded" => true,
            "is_collapsible" => true,
            "id" => "schedule_info",
            "title" => 'Schedule info',
            "children" => array(
                new BaseStyleComponent("select", array(
                    "label" => "Reminder for form",
                    "value" => isset($this->action['id_forms_reminder']) ? $this->action['id_forms_reminder'] : "",
                    "is_required" => true,
                    "name" => "id_forms_reminder",
                    "id" => "id_forms_reminder",
                    "css" => "d-none",
                    "items" => $this->model->get_forms(),
                    "live_search" => true
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Reminder for notification (Needed for surveys with sessions and multiple block schedules)",
                    "value" => isset($this->action['id_formActions']) ? $this->action['id_formActions'] : "",
                    "is_required" => false,
                    "name" => "id_formActions",
                    "id" => "id_formActions",
                    "css" => "d-none",
                    "items" => $this->model->get_notifications(),
                    "live_search" => true
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Type",
                    "is_required" => true,
                    "id" => "type",
                    "value" => isset($this->action["schedule_info"][notificationTypes]) ? $this->action["schedule_info"][notificationTypes] : '',
                    "name" => "schedule_info[notificationTypes]",
                    "items" => $this->model->get_services()->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => notificationTypes))
                )),
                new BaseStyleComponent("input", array(
                    "label" => "Valid after scheduled time (in minutes). [It is used for surveys with multiple sessions and reminders]",
                    "is_required" => false,
                    "id" => "valid",
                    "css" => "d-none",
                    "value" => isset($this->action["schedule_info"]["valid"]) ? $this->action["schedule_info"]["valid"] : '',
                    "name" => "schedule_info[valid]",
                    "type_input" => "number"
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Target group(s)",
                    "name" => "schedule_info[target_groups][]",
                    "id" => "targetGroups",
                    "is_multiple" => true,
                    "is_required" => false,
                    "live_search" => true,
                    "value" => isset($this->action["schedule_info"]['target_groups']) ? $this->action["schedule_info"]['target_groups'] : '',
                    "items" => $this->model->get_groups(),
                    "css" => "mb-3",
                )),
                new BaseStyleComponent("select", array(
                    "label" => "When",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"][actionScheduleTypes]) ? $this->action["schedule_info"][actionScheduleTypes] : '',
                    "name" => "schedule_info[actionScheduleTypes]",
                    "items" => $this->model->get_services()->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => actionScheduleTypes))
                )),
                new BaseStyleComponent("template", array(
                    "path" => __DIR__ . "/tpl_datepicker.php",
                    "items" => array(
                        "name" => 'schedule_info[custom_time]',
                        "value" => isset($this->action["schedule_info"]['custom_time']) ? $this->action["schedule_info"]['custom_time'] : '',
                        "id" => "custom_time"
                    )
                )),
                new BaseStyleComponent("input", array(
                    "label" => "Send After",
                    "css" => 'send_after d-none',
                    "id" => "send_after",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_after']) ? $this->action["schedule_info"]['send_after'] : '',
                    "name" => "schedule_info[send_after]",
                    // "items" => $this->get_time_intervals(),
                    "type_input" => "number"
                )),
                new BaseStyleComponent("select", array(
                    "id" => "send_after_type",
                    "css" => 'd-none',
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_after_type']) ? $this->action["schedule_info"]['send_after_type'] : '',
                    "name" => "schedule_info[send_after_type]",
                    "items" => $this->model->get_services()->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => timePeriod))
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Send on",
                    "css" => 'd-none',
                    "id" => "send_on",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_on']) ? $this->action["schedule_info"]['send_on'] : '',
                    "name" => "schedule_info[send_on]",
                    "items" => $this->get_time_intervals_text(),
                )),
                new BaseStyleComponent("select", array(
                    "id" => "send_on_day",
                    "css" => 'd-none mb-3',
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_on_day']) ? $this->action["schedule_info"]['send_on_day'] : '',
                    "name" => "schedule_info[send_on_day]",
                    "items" => $this->model->get_services()->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => weekdays))
                )),
                new BaseStyleComponent("template", array(
                    "path" => __DIR__ . "/tpl_timepicker.php",
                    "items" => array(
                        "name" => 'schedule_info[send_on_day_at]',
                        "label" => "At",
                        "value" => isset($this->action["schedule_info"]['send_on_day_at']) ? $this->action["schedule_info"]['send_on_day_at'] : '',
                        "id" => "send_on_day_at"
                    )
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Reminder for notification",
                    "is_required" => true,
                    "id" => "linked_action",
                    "value" => isset($this->action["schedule_info"]['linked_action']) ? $this->action["schedule_info"]['linked_action'] : '',
                    "name" => "schedule_info[linked_action]",
                    "items" => $this->model->get_services()->get_db()->fetch_table_as_select_values('view_formActions', 'id', array('action_name'), 'WHERE action_schedule_type_code=:type', array(":type" => actionScheduleJobs_notification)),
                    "live_search" => 1
                )),
                new BaseStyleComponent("input", array(
                    "label" => "From email",
                    "type_input" => "email",
                    "css" => "mt-3",
                    "id" => "from_email",
                    "name" => "schedule_info[from_email]",
                    "value" => isset($this->action["schedule_info"]['from_email']) ? $this->action["schedule_info"]['from_email'] : '',
                    "is_required" => true,
                    "placeholder" => "From email",
                )),
                new BaseStyleComponent("input", array(
                    "label" => "From name",
                    "type_input" => "text",
                    "id" => "from_name",
                    "name" => "schedule_info[from_name]",
                    "value" => isset($this->action["schedule_info"]['from_name']) ? $this->action["schedule_info"]['from_name'] : '',
                    "is_required" => true,
                    "placeholder" => "From name",
                )),
                new BaseStyleComponent("input", array(
                    "label" => "Reply To",
                    "type_input" => "email",
                    "id" => "reply_to",
                    "name" => "schedule_info[reply_to]",
                    "value" => isset($this->action["schedule_info"]['reply_to']) ? $this->action["schedule_info"]['reply_to'] : '',
                    "is_required" => true,
                    "placeholder" => "reply to email",
                )),
                new BaseStyleComponent("input", array(
                    "label" => "URL",
                    "type_input" => "text",
                    "id" => "url",
                    "name" => "schedule_info[url]",
                    "value" => isset($this->action["schedule_info"]['url']) ? $this->action["schedule_info"]['url'] : '',
                    "is_required" => false,
                    "placeholder" => "Url of the page that should be opened",
                )),
                new BaseStyleComponent("input", array(
                    "label" => "To",
                    "type_input" => "text",
                    "id" => "to",
                    "name" => "schedule_info[recipient]",
                    "value" => isset($this->action["schedule_info"]['recipient']) ? $this->action["schedule_info"]['recipient'] : '',
                    "css" => "mt-3",
                    "is_required" => true,
                    "placeholder" => "Please enter the recipient(s). Use @user to retrive automaticaly phone or email. Use " . MAIL_SEPARATOR . " as separator",
                )),
                new BaseStyleComponent("input", array(
                    "label" => "Subject",
                    "type_input" => "text",
                    "id" => "subject",
                    "name" => "schedule_info[subject]",
                    "value" => isset($this->action["schedule_info"]['subject']) ? $this->action["schedule_info"]['subject'] : '',
                    "css" => "mt-3",
                    "is_required" => true,
                    "placeholder" => "Please enter the subject",
                )),
                new BaseStyleComponent("textarea", array(
                    "label" => "Body",
                    "type_input" => "text",
                    "name" => "schedule_info[body]",
                    "id" => "body",
                    "value" => isset($this->action["schedule_info"]['body']) ? $this->action["schedule_info"]['body'] : '',
                    "css" => "mb-3",
                    "placeholder" => "@user_name can be used for showing the user",
                )),
                new BaseStyleComponent("textarea", array(
                    "label" => "Attachments",
                    "type_input" => "json",
                    "name" => "schedule_info[attachments]",
                    "id" => "attachments",
                    "value" => isset($this->action["schedule_info"]['attachments']) ? $this->action["schedule_info"]['attachments'] : '',
                    "css" => "mb-3",
                    "placeholder" => "Add attachment files from assets in array",
                )),
                new BaseStyleComponent("ActionConfigBuilder", array(
                    "label" => "Config",
                    "type_input" => "json",
                    "id" => "config",
                    "name" => "schedule_info[config]",
                    "value" => isset($this->action["schedule_info"]['config']) ? $this->action["schedule_info"]['config'] : '',
                    "css" => "mb-3 actionConfig",
                    "placeholder" => "",
                ))
            )
        ));
    }


    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_moduleFormsAction.php";
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * Render the alert message.
     */
    protected function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

    /**
     * Render the delete form
     */
    private function output_delete_action()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => false,
            "is_collapsible" => true,
            "title" => "Delete Action",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the action name.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "id" => 'deleteForm',
                    "label" => "Delete Action",
                    "url" => $this->model->get_link_url("moduleFormsActions"),
                    "type" => "danger",
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "deleteActionName",
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter action name",
                        )),
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "deleteActionId",
                            "value" => $this->sid,
                        )),
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "mode",
                            "value" => DELETE
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the add action entry form.
     */
    public function output_add_action()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "type" => "warning",
            "title" => $this->mode === INSERT ? 'Add action ' : ('Update action ' . $this->action['id']),
            "children" => array(
                new BaseStyleComponent("form", array(
                    "id" => 'entryForm',
                    "label" => $this->mode === INSERT ? 'Add' : 'Update',
                    "url" => $this->model->get_link_url("moduleFormsActions"),
                    "url_cancel" => $this->model->get_link_url("moduleFormsActions"),
                    "label_cancel" => 'Cancel',
                    "url_type" => 'warning',
                    "type" => $this->mode === INSERT ? 'warning' : 'primary',
                    "children" => array(

                        new BaseStyleComponent("input", array(
                            "label" => "Action name",
                            "type_input" => "text",
                            "name" => "name",
                            "value" => isset($this->action['action_name']) ? $this->action['action_name'] : '',
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter action name",
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "When form",
                            "value" => $this->action['id_forms'] ?? '',
                            "is_required" => true,
                            "live_search" => true,
                            "name" => "id_forms",
                            "items" => $this->model->get_forms(),
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "Is (trigger type)",
                            "value" => $this->action['id_formProjectActionTriggerTypes'] ?? '',
                            "is_required" => true,
                            "name" => "id_formProjectActionTriggerTypes",
                            "items" => $this->get_lookups(actionTriggerTypes),
                        )),
                        new BaseStyleComponent("conditionBuilder", array(
                            "value" => $this->action['condition'] ?? '',
                            "name" => 'condition'
                        )),
                        new BaseStyleComponent("textarea", array(
                            "value" => $this->action['jquery_builder_json'] ?? '',
                            "name" => 'jquery_builder_json',
                            "css" => "d-none"
                        )),
                        new BaseStyleComponent("ActionConfigBuilder", array(
                            "label" => "Config",
                            "type_input" => "json",
                            "id" => "config",
                            "name" => "schedule_info",
                            "value" => isset($this->action["schedule_info"]) ? $this->action["schedule_info"] : '',
                            "css" => "mb-3 actionConfig",
                            "placeholder" => "",
                        )),
                        // new BaseStyleComponent("select", array(
                        //     "label" => "For group(s)",
                        //     "name" => "id_groups[]",
                        //     "is_multiple" => true,
                        //     "is_required" => false,
                        //     "live_search" => true,
                        //     "value" => explode(',', $this->action['id_groups'] ?? ''),
                        //     "items" => $this->model->get_groups(),
                        //     "css" => "mb-3",
                        // )),
                        // new BaseStyleComponent("select", array(
                        //     "label" => "Schedule",
                        //     "name" => "id_formActionScheduleTypes",
                        //     "id" => "id_formActionScheduleTypes",
                        //     "value" => isset($this->action['id_formActionScheduleTypes']) ? $this->action['id_formActionScheduleTypes'] : $this->model->get_services()->get_db()->get_lookup_id_by_value(actionScheduleJobs, 'nothing'),
                        //     "items" => $this->get_lookups(actionScheduleJobs),
                        //     "css" => "mb-3",
                        // )),
                        // $this->get_schedule_info_card(),
                        // new BaseStyleComponent("input", array(
                        //     "type_input" => "hidden",
                        //     "name" => "id",
                        //     "value" => $this->sid,
                        // )),
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "mode",
                            "value" => $this->mode
                        ))
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        if (empty($local)) {
            $local = array(
                __DIR__ . "/js/formAction.js",
            );
        }
        return parent::get_js_includes($local);
    }

    /**
     * Render the add action entry form view.
     */
    public function output_action_view()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "url_edit" => $this->model->get_link_url("moduleFormsAction", array("mode" => UPDATE, "sid" => $this->sid)),
            "title" => 'Action &nbsp;<code>' . $this->action['action_name'] . '</code>',
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Action name",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->action['action_name']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "When form",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->action['form_name']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Is (trigger type)",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->action['trigger_type']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "For groups",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->action['groups']
                    ))),
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Schedule",
                    "name" => "id_formActionScheduleTypes",
                    "id" => "id_formActionScheduleTypes",
                    "value" => isset($this->action['id_formActionScheduleTypes']) ? $this->action['id_formActionScheduleTypes'] : $this->model->get_services()->get_db()->get_lookup_id_by_value(actionScheduleJobs, 'nothing'),
                    "items" => $this->get_lookups(actionScheduleJobs),
                    "css" => "mb-3",
                    "disabled" => true
                )),
                $this->get_schedule_info_card_view()
            )
        ));
        $form->output_content();
    }
}
?>
