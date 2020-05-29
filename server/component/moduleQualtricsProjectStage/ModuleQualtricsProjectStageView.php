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
class ModuleQualtricsProjectStageView extends ModuleQualtricsProjectView
{

    /* Private Properties *****************************************************/
    /**
     * project id,
     * if it is > 0  edit/delete project page     
     */
    private $pid;

    /**
     * stage id, 
     * if it is > 0  edit/delete project page     
     */
    private $sid;

    /**
     * The mode type of the form EDIT, DELETE, INSERT, VIEW     
     */
    private $mode;

    /**
     * the current selcted stage
     */
    private $stage;

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
        $this->stage = $this->model->get_services()->get_db()->select_by_uid("view_qualtricsStages", $this->sid);
        $this->stages = $this->model->get_stages($this->pid);
        if ($this->stage) {
            $this->stage['notification'] = json_decode($this->stage['notification'], true);
            $this->stage['reminder'] = json_decode($this->stage['reminder'], true);
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
        foreach (range(0, 60) as $range) {
            array_push($arr, array("value" => $range, "text" => $range));
        }
        return $arr;
    }

    /**
     * get notification card.
     * @param bool true = notification, false = reminder
     *
     *  @retval card
     */
    private function get_notification_card($isNotification)
    {
        $type =  $isNotification ? 'notification' : 'reminder';
        return new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => $isNotification,
            "is_collapsible" => true,
            "title" => $isNotification ? 'Notification' : 'Reminder',
            "children" => array(
                new BaseStyleComponent("select", array(
                    "label" => ($isNotification ? "Notification" : "Reminder") . " type",
                    "value" => isset($this->stage[$type]) ? $this->stage[$type]['type'] : '',
                    "name" => $isNotification ? "notification[type]" : "reminder[type]",
                    "items" => $this->get_lookups('notificationTypes'),
                )),
                new BaseStyleComponent("select", array(
                    "label" => "Send After",
                    "value" => isset($this->stage[$type]) ? $this->stage[$type]['delay_value'] : '',
                    "name" => $isNotification ? "notification[delay_value]" : "reminder[delay_value]",
                    "items" => $this->get_time_intervals(),
                )),
                new BaseStyleComponent("select", array(
                    "value" => isset($this->stage[$type]) ? $this->stage[$type]['delay_value_type'] : '',
                    "name" => $isNotification ? "notification[delay_value_type]" : "reminder[delay_value_type]",
                    "items" => $this->get_lookups("timePeriod"),
                )),
                new BaseStyleComponent("input", array(
                    "label" => "Subject",
                    "type_input" => "text",
                    "name" => $isNotification ? "notification[subject]" : "reminder[subject]",
                    "value" => isset($this->stage[$type]) ? $this->stage[$type]['subject'] : '',
                    "css" => "mt-3",
                    "placeholder" => "Please enter the subject",
                )),
                new BaseStyleComponent("textarea", array(
                    "label" => "Body",
                    "type_input" => "text",
                    "name" => $isNotification ? "notification[body]" : "reminder[body]",
                    "value" => isset($this->stage[$type]) ? $this->stage[$type]['body'] : '',
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
    private function get_notification_card_view($isNotification)
    {
        $type =  $isNotification ? 'notification' : 'reminder';
        return new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => $isNotification,
            "is_collapsible" => true,
            "title" => $isNotification ? 'Notification' : 'Reminder',
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => ($isNotification ? "Notification" : "Reminder") . " type",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->stage[$type]) ? $this->get_lookup($this->stage[$type]['type']) : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Send After",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->stage[$type]) ? $this->stage[$type]['delay_value'] . ' ' . (isset($this->stage[$type]) ? $this->get_lookup($this->stage[$type]['delay_value_type']) : '') : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Subject",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->stage[$type]) ? $this->stage[$type]['subject'] : ''
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Body",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => isset($this->stage[$type]) ? $this->stage[$type]['body'] : ''
                    ))),
                ))
            )
        ));
    }

    /**
     * Render the delete form
     */
    private function output_delete_stage()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => false,
            "is_collapsible" => true,
            "title" => "Delete Stage",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the project name.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "id" => 'deleteForm',
                    "label" => "Delete Stage",
                    "url" => $this->model->get_link_url("moduleQualtricsProjectStage", array("pid" => $this->pid, "mode" => SELECT)),
                    "type" => "danger",
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "deleteStageName",
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter stage name",
                        )),
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "deleteStageId",
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
            require __DIR__ . "/tpl_moduleQualtricsProjectStage.php";
        }
    }

    /**
     * Render the add stage entry form.
     */
    public function output_add_stage()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "type" => "warning",
            "title" => $this->mode === INSERT ? 'Add stage ' : ('Update stage ' . $this->stage['id']) . (' for project: ' . $this->project['name']),
            "children" => array(
                new BaseStyleComponent("form", array(
                    "id" => 'entryForm',
                    "label" => $this->mode === INSERT ? 'Add' : 'Update',
                    "url" => $this->model->get_link_url("moduleQualtricsProjectStage", array("pid" => $this->pid, "mode" => SELECT)),
                    "url_cancel" => $this->model->get_link_url("moduleQualtricsProject", array("pid" => $this->pid, "mode" => SELECT)),
                    "label_cancel" => 'Cancel',
                    "url_type" => 'warning',
                    "type" => $this->mode === INSERT ? 'warning' : 'primary',
                    "children" => array(
                        new BaseStyleComponent("select", array(
                            "label" => "Stage type",
                            "value" => $this->stage['id_qualtricsProjectStageTypes'],
                            "is_required" => true,
                            "name" => "id_qualtricsProjectStageTypes",
                            "items" => $this->get_lookups('qualtricsProjectStageTypes'),
                        )),
                        new BaseStyleComponent("input", array(
                            "label" => "Stage name",
                            "type_input" => "text",
                            "name" => "name",
                            "value" => $this->stage['stage_name'],
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter stage name",
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "When survey",
                            "value" => $this->stage['survey_id'],
                            "is_required" => true,
                            "name" => "id_qualtricsSurveys",
                            "items" => $this->get_surveys(),
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "Is (trigger type)",
                            "value" => $this->stage['trigger_type'],
                            "is_required" => true,
                            "name" => "id_qualtricsProjectStageTriggerTypes",
                            "items" => $this->get_lookups('qualtricsProjectStageTriggerTypes'),
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "For groups (condition, selct if needed)",
                            "name" => "id_groups[]",
                            "is_multiple" => true,
                            "value" => explode(';', $this->stage['id_groups']),
                            "items" => $this->get_groups(),
                            "css" => "mb-3",
                        )),
                        $this->get_notification_card(true),
                        $this->get_notification_card(false),
                        new BaseStyleComponent("select", array(
                            "label" => "Additional functions",
                            //"value" => $this->projectStage['stage'],
                            "is_required" => false,
                            "name" => "id_functions[]",
                            "is_multiple" => true,
                            "value" => explode(';', $this->stage['id_functions']),
                            "items" => $this->get_lookups('qualtricsProjectStageAdditionalFunction'),
                        )),
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
     * Render the add stage entry form view.
     */
    public function output_add_stage_view()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "url_edit" => $this->model->get_link_url("moduleQualtricsProjectStage", array("pid" => $this->pid, "mode" => UPDATE, "sid" => $this->sid)),
            "title" => 'Stage &nbsp;<code>' . $this->stage['stage_name'] . '</code>&nbsp; for project &nbsp;<code>' . $this->stage['project_name'] . '</code>',
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Stage type",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->stage['stage_type']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Survey name",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->stage['stage_name']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "When survey",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->stage['survey_name']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Is (trigger type)",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->stage['trigger_type']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "For groups",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->stage['groups']
                    ))),
                )),
                $this->get_notification_card_view(true),
                $this->get_notification_card_view(false),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Additional functions",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->stage['functions']
                    ))),
                ))
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
                __DIR__ . "/js/qualtricsStage.js",
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
}
?>
