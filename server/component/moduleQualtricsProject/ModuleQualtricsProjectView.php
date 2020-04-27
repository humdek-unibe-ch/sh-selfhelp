<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../moduleQualtrics/ModuleQualtricsView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class ModuleQualtricsProjectView extends ModuleQualtricsView
{

    /* Private Properties *****************************************************/
    /**
     * project id, if it is null, show all projects, if it is = 0, create new project
     * if it is > 0  edit/delete project page     
     */
    private $pid;

    /**
     * The mode type of the form EDIT, DELETE, INSERT, VIEW     
     */
    private $mode;

    /**
     * the current selct project
     */
    private $project;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller, $pid, $mode)
    {
        parent::__construct($model, $controller);
        $this->pid = $pid;
        $this->mode = $mode;
        $this->project = $this->model->get_db()->select_by_uid("qualtricsProjects", $this->pid);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the delete form
     */
    private function output_delete_form()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => false,
            "is_collapsible" => true,
            "title" => "Delete Project",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the project name.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Delete Project",
                    "url" => $this->model->get_link_url("moduleQualtricsProject"),
                    "type" => "danger",
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "deleteProjectName",
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter project name",
                        )),
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "deleteProjectId",
                            "value" => $this->pid,
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
     * Render the entry form
     */
    private function output_entry_form()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "type" => "warning",
            "title" => $this->mode === INSERT ? 'New Qualtrics Project' : 'Qualtrics Project ID: ' . $this->project['id'],
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => $this->mode === INSERT ? 'Create' : 'Update',
                    "url" => $this->model->get_link_url("moduleQualtricsProject"),
                    "url_cancel" => $this->model->get_link_url("moduleQualtricsProject", array("pid" => $this->pid, "mode" => SELECT)),
                    "label_cancel" => 'Cancel',
                    "type" => $this->mode === INSERT ? 'primary' : 'warning',
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "label" => "Project name:",
                            "type_input" => "text",
                            "name" => "name",
                            "value" => $this->project['name'],
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter project name",
                        )),
                        new BaseStyleComponent("input", array(
                            "label" => "API mailing group:",
                            "type_input" => "text",
                            "name" => "api_mailing_group_id",
                            "value" => $this->project['api_mailing_group_id'],
                            "css" => "mb-3",
                            "placeholder" => "Enter API mailing group id",
                        )),
                        new BaseStyleComponent("textarea", array(
                            "label" => "Project description:",
                            "type_input" => "text",
                            "name" => "description",
                            "value" => $this->project['description'],
                            "css" => "mb-3",
                            "placeholder" => "Enter project description",
                        )),
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "id",
                            "value" => $this->pid,
                        )),
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "mode",
                            "value" => $this->mode
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the entry form
     */
    private function output_entry_form_view()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "url_edit" => $this->model->get_link_url("moduleQualtricsProject", array("pid" => $this->pid, "mode" => UPDATE)),
            "title" => 'Qualtrics Project ID: ' . $this->project['id'],
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Project name",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->project['name']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "API mailing group",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->project['api_mailing_group_id']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Project description",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->project['description']
                    ))),
                )),
            )
        ));
        $form->output_content();
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
     * call the navbar render
     */
    public function output_navbar($title)
    {
        parent::output_navbar('Projects');
    }

    /**
     * render the page content
     */
    public function output_page_content()
    {
        $cancel_url = $this->model->get_link_url("moduleQualtricsProject");
        $project = array(
            "id" => $this->pid,
            "name" => "",
            "api_mailing_group_id" => "",
            "description" => ""
        );
        $action_url = $this->model->get_link_url("moduleQualtricsProject");
        // if ($this->pid === null) {
        //     $this->mode = SELECT;
        //     require __DIR__ . "/tpl_qualtricsProjects.php";
        // } else if ($this->pid === 0) {
        //     $this->mode = INSERT;
        //     require __DIR__ . "/tpl_qulatricsProject_entry.php";
        // } else if ($this->pid > 0) {
        //     $this->mode = UPDATE;            
        //     require __DIR__ . "/tpl_qulatricsProject_entry.php";
        // }
        if ($this->mode === null) {
            require __DIR__ . "/tpl_qualtricsProjects.php";
        } else {
            require __DIR__ . "/tpl_qulatricsProject_entry.php";
        }
    }

    /**
     * Render the sidebar buttons
     */
    public function output_side_buttons()
    {
        $button = new BaseStyleComponent("button", array(
            "label" => "Create New Project",
            "url" => $this->model->get_link_url("moduleQualtricsProject", array("pid" => 0)),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $button->output_content();
    }

    /**
     * Render the qualtrics projects table content.
     */
    private function output_projects_rows()
    {
        foreach ($this->model->get_projects() as $project) {
            require __DIR__ . "/tpl_qualtricsProject_row.php";
        }
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
            $local = array(__DIR__ . "/js/qualtricsProjects.js");
        }
        return parent::get_js_includes($local);
    }
}
?>
