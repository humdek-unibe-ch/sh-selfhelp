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
    private $aid;

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
    public function __construct($model, $controller, $mode, $aid)
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
        $this->aid = $aid;
        if ($this->aid) {
            $this->action = $this->model->get_services()->get_db()->select_by_uid("view_formActions", $this->aid);
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
                            "value" => $this->aid,
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
                            "name" => 'condition',
                            "css" => "d-none"
                        )),
                        new BaseStyleComponent("textarea", array(
                            "value" => $this->action['condition_jquery_builder_json'] ?? '',
                            "name" => 'condition_jquery_builder_json',
                            "css" => "d-none"
                        )),
                        new BaseStyleComponent("JobConfig", array(
                            "type_input" => "json",
                            "id" => "config",
                            "name" => "config",
                            "value" => isset($this->action["config"]) ? $this->action["config"] : '',
                            "css" => "jobConfig",
                            "placeholder" => "",
                        )),
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "id",
                            "value" => $this->aid,
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
    public function output_action_view()
    {
        if ($this->action) {
            $form = new BaseStyleComponent("card", array(
                "css" => "mb-3",
                "is_expanded" => true,
                "is_collapsible" => false,
                "url_edit" => $this->model->get_link_url("moduleFormsAction", array("mode" => UPDATE, "aid" => $this->aid)),
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
                    new BaseStyleComponent("JobConfig", array(
                        "type_input" => "json",
                        "id" => "config",
                        "name" => "config",
                        "value" => isset($this->action["config"]) ? $this->action["config"] : '',
                        "css" => "jobConfig view-mode",
                        "placeholder" => "",
                    ))
                )
            ));
            $form->output_content();
        }
    }

}
?>
