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
class ModuleQualtricsProjectStageView extends BaseView
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
        $this->project = $this->model->get_services()->get_db()->select_by_uid("qualtricsProjects", $this->pid);
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
     * generate the stages.
     *
     *  @retval array
     *  value int,
     *  text string
     */
    private function get_stages()
    {
        $stages = array();
        array_push($stages, array("value" => 1, "text" => "Stage Initialization"));
        foreach (range(2, 12) as $number) {
            array_push($stages, array("value" => $number, "text" => "Stage " . $number));
        }
        return $stages;
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
        require __DIR__ . "/tpl_moduleQualtricsProjectStage.php";
    }

    /**
     * Render the footer view.
     */
    public function output_add_stage()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "type" => "warning",
            "title" => 'Add stage for project: ' . $this->project['name'],
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => $this->mode === INSERT ? 'Create' : 'Update',
                    "url" => $this->model->get_link_url("moduleQualtricsProject"),
                    "url_cancel" => $this->model->get_link_url("moduleQualtricsProject", array("pid" => $this->pid, "mode" => SELECT)),
                    "label_cancel" => 'Cancel',
                    "type" => $this->mode === INSERT ? 'primary' : 'warning',
                    "children" => array(                        
                        new BaseStyleComponent("select", array(
                            "label" => "Stage",
                            //"value" => $this->projectStage['stage'],
                            "is_required" => true,
                            "name" => "stage",
                            "items" => $this->get_stages(),
                        )),
                        new BaseStyleComponent("input", array(
                            "label" => "Stage name",
                            "type_input" => "text",
                            "name" => "stage_name",
                            //"value" => $this->projectStage['stage_name'],
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter stage name",
                        )),
                        new BaseStyleComponent("select", array(
                            "label" => "Survey",
                            //"value" => $this->projectStage['stage'],
                            "is_required" => true,
                            "name" => "stage",
                            "items" => $this->get_surveys(),
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
}
?>
