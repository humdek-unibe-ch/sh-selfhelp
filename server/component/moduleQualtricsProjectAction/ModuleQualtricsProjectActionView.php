<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../moduleQualtricsProject/ModuleQualtricsProjectView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class ModuleQualtricsProjectActionView extends ModuleQualtricsProjectView
{

    /* Private Properties *****************************************************/
    /**
     * project id,
     * if it is > 0  edit/delete project page     
     */
    private $pid;

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
     * the current selected project
     */
    private $project;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller, $pid, $mode, $sid)
    {
        parent::__construct($model, $controller, $pid, $mode);
        $this->pid = $pid;
        $this->sid = $sid;
        $this->mode = $mode;
        $this->project = $this->model->get_services()->get_db()->select_by_uid("qualtricsProjects", $this->pid);
        $this->action = $this->model->get_services()->get_db()->select_by_uid("view_qualtricsActions", $this->sid);
        $this->actions = $this->model->get_actions($this->pid);
        if ($this->action) {
            $this->action['schedule_info'] = json_decode($this->action['schedule_info'], true);
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Render the asset list.
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css' or 'asset').
     */
    private function output($mode)
    {
        echo $mode;
    }

    /**
     * get user groups from the database.
     *
     *  @retval array
     *  value int,
     *  text string
     */
    private function get_groups()
    {
        $groups = array();
        foreach ($this->model->get_services()->get_db()->select_table("groups") as $group) {
            array_push($groups, array("value" => intval($group['id']), "text" => $group['name']));
        }
        return $groups;
    }

    /**
     * get time intervals from 0 to 60
     *
     *  @retval array
     *  value int,
     *  text string
     */
    private function get_time_intervals()
    {
        $arr = array();
        foreach (range(1, 60) as $range) {
            array_push($arr, array("value" => $range, "text" => $range));
        }
        return $arr;
    }

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
     * get notification card.
     * @param bool true = notification, false = reminder
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
                    "label" => "Reminder for survey",
                    "value" => isset($this->action['id_qualtricsSurveys_reminder']) ? $this->action['id_qualtricsSurveys_reminder'] : "",
                    "is_required" => true,
                    "name" => "id_qualtricsSurveys_reminder",
                    "id" => "id_qualtricsSurveys_reminder",
                    "css" => "d-none",
                    "items" => $this->get_surveys(),
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Type",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['notificationTypes']) ? $this->action["schedule_info"]['notificationTypes'] : '',
                    "name" => "schedule_info[notificationTypes]",
                    "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'),'WHERE type_code=:tcode', array(":tcode"=>'notificationTypes'))
                )),
                new BaseStyleComponent("select", array(
                    "label" => "When",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['qualtricScheduleTypes']) ? $this->action["schedule_info"]['qualtricScheduleTypes'] : '',
                    "name" => "schedule_info[qualtricScheduleTypes]",
                    "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'),'WHERE type_code=:tcode', array(":tcode"=>'qualtricScheduleTypes'))
                )),
                new BaseStyleComponent("template", array(
                    "path" => __DIR__ . "/tpl_datepicker.php",
                    "items" => array(
                        "name" => 'schedule_info[custom_time]',
                        "value" => isset($this->action["schedule_info"]['custom_time']) ? $this->action["schedule_info"]['custom_time'] : '',
                        "id" => "custom_time"
                    )
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Send After",
                    "css" => 'send_after d-none',
                    "id" => "send_after",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_after']) ? $this->action["schedule_info"]['send_after'] : '',
                    "name" => "schedule_info[send_after]",
                    "items" => $this->get_time_intervals(),
                )),
                new BaseStyleComponent("select", array(
                    "id" => "send_after_type",
                    "css" => 'd-none',
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_after_type']) ? $this->action["schedule_info"]['send_after_type'] : '',
                    "name" => "schedule_info[send_after_type]",
                    "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'),'WHERE type_code=:tcode', array(":tcode"=>'timePeriod'))
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
                    "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'),'WHERE type_code=:tcode', array(":tcode"=>'weekdays'))
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
                new BaseStyleComponent("input", array(
                    "label" => "From email",
                    "type_input" => "email",
                    "css" => "mt-3",
                    "name" => "schedule_info[from_email]",
                    "value" => isset($this->action["schedule_info"]['from_email']) ? $this->action["schedule_info"]['from_email'] : '',
                    "is_required" => true,
                    "placeholder" => "From email",
                )),
                new BaseStyleComponent("input", array(
                    "label" => "From name",
                    "type_input" => "text",
                    "name" => "schedule_info[from_name]",
                    "value" => isset($this->action["schedule_info"]['from_name']) ? $this->action["schedule_info"]['from_name'] : '',
                    "is_required" => true,
                    "placeholder" => "From name",
                )),
                new BaseStyleComponent("input", array(
                    "label" => "Reply To",
                    "type_input" => "email",
                    "name" => "schedule_info[reply_to]",
                    "value" => isset($this->action["schedule_info"]['reply_to']) ? $this->action["schedule_info"]['reply_to'] : '',
                    "is_required" => true,
                    "placeholder" => "reply to email",
                )),
                new BaseStyleComponent("input", array(
                    "label" => "To",
                    "type_input" => "text",
                    "name" => "schedule_info[recipient]",
                    "value" => isset($this->action["schedule_info"]['recipient']) ? $this->action["schedule_info"]['recipient'] : '',
                    "css" => "mt-3",
                    "is_required" => true,
                    "placeholder" => "Please enter the recipient(s). Use @user to retrive automaticaly phone or email. Use " . MAIL_SEPARATOR . " as separator",
                )),
                new BaseStyleComponent("input", array(
                    "label" => "Subject",
                    "type_input" => "text",
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
                    "value" => isset($this->action["schedule_info"]['body']) ? $this->action["schedule_info"]['body'] : '',
                    "css" => "mb-3",
                    "placeholder" => "@user_name can be used for showing the user \n@survey_(type qualtrics survey id) can be used to automatically generate the link",
                )),
            )
        ));
    }

    /**
     * get notification card view.
     * @param bool true = notification, false = reminder
     *
     *  @retval card
     */
    private function get_schedule_info_card_view()
    {
        return new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => true,
            "title" => 'Schedule info',
            "children" => array(
                new BaseStyleComponent("select", array(
                    "label" => "Reminder for survey",
                    "value" => isset($this->action['id_qualtricsSurveys_reminder']) ? $this->action['id_qualtricsSurveys_reminder'] : "",
                    "is_required" => true,
                    "name" => "id_qualtricsSurveys_reminder",
                    "id" => "id_qualtricsSurveys_reminder",
                    "css" => "d-none",
                    "items" => $this->get_surveys(),
                    "disabled" => true
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Type",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['notificationTypes']) ? $this->action["schedule_info"]['notificationTypes'] : '',
                    "name" => "schedule_info[notificationTypes]",
                    "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'),'WHERE type_code=:tcode', array(":tcode"=>'notificationTypes')),
                    "disabled" => true
                )),
                new BaseStyleComponent("select", array(
                    "label" => "When",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['qualtricScheduleTypes']) ? $this->action["schedule_info"]['qualtricScheduleTypes'] : '',
                    "name" => "schedule_info[qualtricScheduleTypes]",
                    "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'),'WHERE type_code=:tcode', array(":tcode"=>'qualtricScheduleTypes')),
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
                new BaseStyleComponent("select", array(
                    "label" => "Send After",
                    "css" => 'send_after d-none',
                    "id" => "send_after",
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_after']) ? $this->action["schedule_info"]['send_after'] : '',
                    "name" => "schedule_info[send_after]",
                    "items" => $this->get_time_intervals(),
                    "disabled" => true
                )),
                new BaseStyleComponent("select", array(
                    "id" => "send_after_type",
                    "css" => 'd-none',
                    "is_required" => true,
                    "value" => isset($this->action["schedule_info"]['send_after_type']) ? $this->action["schedule_info"]['send_after_type'] : '',
                    "name" => "schedule_info[send_after_type]",
                    "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'),'WHERE type_code=:tcode', array(":tcode"=>'timePeriod')),
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
                    "items" => $this->model->get_db()->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'),'WHERE type_code=:tcode', array(":tcode"=>'weekdays')),
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
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "From Email",
                    "locale" => "",
                    "css" => "mt-3",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['from_email']) ? $this->action["schedule_info"]['from_email'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "From Name",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['from_name']) ? $this->action["schedule_info"]['from_name'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Reply To",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['reply_to']) ? $this->action["schedule_info"]['reply_to'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "To",
                    "locale" => "",
                    "css" => "mt-3",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->action["schedule_info"]['recipient']) ? $this->action["schedule_info"]['recipient'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Subject",
                    "locale" => "",
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
                new BaseStyleComponent("textarea", array(
                    "label" => "Body",
                    "type_input" => "text",
                    "name" => "schedule_info[body]",
                    "value" => isset($this->action["schedule_info"]['body']) ? $this->action["schedule_info"]['body'] : '',
                    "css" => "d-none",
                    "placeholder" => "@user_name can be used for showing the user \n@survey_(type qualtrics survey id) can be used to automatically generate the link",
                ))
            )
        ));
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
                    "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the project name.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "id" => 'deleteForm',
                    "label" => "Delete Action",
                    "url" => $this->model->get_link_url("moduleQualtricsProjectAction", array("pid" => $this->pid, "mode" => SELECT)),
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

    private function get_lookup($id)
    {
        return $this->model->get_services()->get_db()->select_by_uid("lookups", $id)['lookup_value'];
    }

    /**
     * get surveys from the database.
     *
     *  @retval array
     *  value int,
     *  text string
     */
    private function get_surveys()
    {
        $surveys = array();
        foreach ($this->model->get_services()->get_db()->select_table("qualtricsSurveys") as $survey) {
            array_push($surveys, array("value" => $survey['id'], "text" => $survey['name']));
        }
        return $surveys;
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/../moduleQualtrics/tpl_moduleQualtrics.php";
    }

    /**
     * render the page content
     */
    public function output_page_content()
    {
        if ($this->mode === SELECT && $this->sid === null) {
            require __DIR__ . "/../moduleQualtricsProject/tpl_qulatricsProject_entry.php";
        } else {
            require __DIR__ . "/tpl_moduleQualtricsProjectAction.php";
        }
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
            "title" => $this->mode === INSERT ? 'Add action ' : ('Update action ' . $this->action['id']) . (' for project: ' . $this->project['name']),
            "children" => array(
                new BaseStyleComponent("form", array(
                    "id" => 'entryForm',
                    "label" => $this->mode === INSERT ? 'Add' : 'Update',
                    "url" => $this->model->get_link_url("moduleQualtricsProjectAction", array("pid" => $this->pid, "mode" => SELECT)),
                    "url_cancel" => $this->model->get_link_url("moduleQualtricsProject", array("pid" => $this->pid, "mode" => SELECT)),
                    "label_cancel" => 'Cancel',
                    "url_type" => 'warning',
                    "type" => $this->mode === INSERT ? 'warning' : 'primary',
                    "children" => array(

                        new BaseStyleComponent("input", array(
                            "label" => "Action name",
                            "type_input" => "text",
                            "name" => "name",
                            "value" => $this->action['action_name'],
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter action name",
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "When survey",
                            "value" => $this->action['survey_id'],
                            "is_required" => true,
                            "name" => "id_qualtricsSurveys",
                            "items" => $this->get_surveys(),
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "Is (trigger type)",
                            "value" => $this->action['id_qualtricsProjectActionTriggerTypes'],
                            "is_required" => true,
                            "name" => "id_qualtricsProjectActionTriggerTypes",
                            "items" => $this->get_lookups('qualtricsProjectActionTriggerTypes'),
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "For group(s)",
                            "name" => "id_groups[]",
                            "is_multiple" => true,
                            "is_required" => true,
                            "value" => explode(',', $this->action['id_groups']),
                            "items" => $this->get_groups(),
                            "css" => "mb-3",
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "Send",
                            "name" => "id_qualtricsActionScheduleTypes",
                            "id" => "id_qualtricsActionScheduleTypes",
                            "value" => isset($this->action['id_qualtricsActionScheduleTypes']) ? $this->action['id_qualtricsActionScheduleTypes'] : $this->model->get_services()->get_db()->get_lookup_id_by_value('qualtricsActionScheduleTypes', 'nothing'),
                            "items" => $this->get_lookups('qualtricsActionScheduleTypes'),
                            "css" => "mb-3",
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "Additional functions",
                            "is_required" => false,
                            "name" => "id_functions[]",
                            "is_multiple" => true,
                            "value" => explode(';', $this->action['id_functions']),
                            "items" => $this->get_lookups('qualtricsProjectActionAdditionalFunction'),
                        )),
                        $this->get_schedule_info_card(),                        
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "id",
                            "value" => $this->sid,
                        )),
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
     * Render the add action entry form view.
     */
    public function output_add_action_view()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "url_edit" => $this->model->get_link_url("moduleQualtricsProjectAction", array("pid" => $this->pid, "mode" => UPDATE, "sid" => $this->sid)),
            "title" => 'Action &nbsp;<code>' . $this->action['action_name'] . '</code>&nbsp; for project &nbsp;<code>' . $this->action['project_name'] . '</code>',
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Action name",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->action['action_name']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "When survey",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->action['survey_name']
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
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Send",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->action['action_schedule_type']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Additional functions",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->action['functions']
                    ))),
                )),
                $this->get_schedule_info_card_view()                
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
                __DIR__ . "/js/qualtricsAction.js",
                __DIR__ . "/../moduleQualtricsProject/js/qualtricsProjects.js",
                __DIR__ . "/../js/simplemde.min.js"
            );
        }
        return parent::get_js_includes($local);
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/../css/simplemde.min.css");
        return parent::get_css_includes($local);
    }

    /**
     * Render the sidebar buttons
     */
    public function output_side_buttons()
    {
        $backToProject = new BaseStyleComponent("button", array(
            "label" => "Back to project",
            "url" => $this->model->get_link_url("moduleQualtricsProject", array("mode" => SELECT, "pid" => $this->pid)),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $backToProject->output_content();
    }
}
?>
