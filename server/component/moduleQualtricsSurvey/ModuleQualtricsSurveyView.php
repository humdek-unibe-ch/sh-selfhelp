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
class ModuleQualtricsSurveyView extends ModuleQualtricsView
{

    /* Private Properties *****************************************************/
    /**
     * survey id, if it is null, show all surveys, if it is = 0, create new survey
     * if it is > 0  edit/delete survey page     
     */
    private $sid;

    /**
     * The mode type of the form EDIT, DELETE, INSERT, VIEW     
     */
    private $mode;

    /**
     * the current selct survey
     */
    private $survey;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller, $sid)
    {
        parent::__construct($model, $controller);
        $this->sid = $sid;
        $this->survey = $this->model->get_db()->select_by_uid("qualtricsSurveys", $this->sid);
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
            "title" => "Delete Survey",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the survey name.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Delete Survey",
                    "url" => $this->model->get_link_url("moduleQualtricsSurvey"),
                    "type" => "danger",
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "deleteSurveyName",
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter survey name",
                        )),
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "deleteSurveyId",
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
     * Render the entry form
     */
    private function output_entry_form()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => $this->mode === INSERT ? 'New Qualtrics Survey' : 'Qualtrics Survey ID: ' . $this->survey['id'],
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => $this->mode === INSERT ? 'Create' : 'Update',
                    "url" => $this->model->get_link_url("moduleQualtricsSurvey"),
                    "url_cancel" => $this->model->get_link_url("moduleQualtricsSurvey"),
                    "label_cancel" => 'Cancel',
                    "type" => $this->mode === INSERT ? 'primary' : 'warning',
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "label" => "Survey name:",
                            "type_input" => "text",
                            "name" => "name",
                            "value" => $this->survey['name'],
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter survey name",
                        )),
                        new BaseStyleComponent("input", array(
                            "label" => "Qualtrics survey id:",
                            "type_input" => "text",
                            "name" => "qualtrics_survey_id",
                            "is_required" => true,
                            "value" => $this->survey['qualtrics_survey_id'],
                            "css" => "mb-3",
                            "placeholder" => "Enter qualtrics survey id",
                        )),
                        new BaseStyleComponent("textarea", array(
                            "label" => "Survey description:",
                            "type_input" => "text",
                            "name" => "description",
                            "value" => $this->survey['description'],
                            "css" => "mb-3",
                            "placeholder" => "Enter survey description",
                        )),
                        new BaseStyleComponent("input", array(
                            "label" => "Subject variable name:",
                            "type_input" => "text",
                            "name" => "subject_variable",
                            "value" => $this->survey['subject_variable'],
                            "css" => "mb-3",
                            "placeholder" => "Enter subject variable name",
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
                        )),
                    )
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
        parent::output_navbar('Surveys');
    }

    /**
     * render the page content
     */
    public function output_page_content()
    {
        $cancel_url = $this->model->get_link_url("moduleQualtricsSurvey");
        $survey = array(
            "id" => $this->sid,
            "name" => "",
            "api_mailing_group_id" => "",
            "description" => ""
        );
        $action_url = $this->model->get_link_url("moduleQualtricsSurvey");
        if ($this->sid === null) {
            $this->mode = SELECT;
            require __DIR__ . "/tpl_qualtricsSurveys.php";
        } else if ($this->sid === 0) {
            $this->mode = INSERT;
            require __DIR__ . "/tpl_qulatricsSurvey_entry.php";
        } else if ($this->sid > 0) {
            $this->mode = UPDATE;            
            require __DIR__ . "/tpl_qulatricsSurvey_entry.php";
        }
    }

    /**
     * Render the sidebar buttons
     */
    public function output_side_buttons()
    {
        $button = new BaseStyleComponent("button", array(
            "label" => "Create New Survey",
            "url" => $this->model->get_link_url("moduleQualtricsSurvey", array("sid" => 0)),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $button->output_content();
    }

    /**
     * Render the qualtrics surveys table content.
     */
    private function output_surveys_rows()
    {
        foreach ($this->model->get_surveys() as $survey) {
            require __DIR__ . "/tpl_qualtricsSurvey_row.php";
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
            $local = array(__DIR__ . "/js/qualtricsSurveys.js");
        }
        return parent::get_js_includes($local);
    }
}
?>
