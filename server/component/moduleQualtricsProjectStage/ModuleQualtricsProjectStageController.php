<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the group insert component.
 */
class ModuleQualtricsProjectStageController extends BaseController
{
    /* Private Properties *****************************************************/


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $pid)
    {
        parent::__construct($model);
        if (isset($_POST['mode']) && !$this->check_acl($_POST['mode'])) {
            return false;
        }
        if (isset($_POST['notification']) && $_POST['notification']['body'] == '' && $_POST['notification']['subject'] == '') {
            //notification not set
            $_POST['notification'] = null;
        }
        if (isset($_POST['reminder']) && $_POST['reminder']['body'] == '' && $_POST['reminder']['subject'] == '') {
            //reminder not set
            $_POST['reminder'] = null;
        }
        if (
            isset($_POST['mode']) && $_POST['mode'] === INSERT &&
            isset($_POST['id_qualtricsProjectStageTypes']) &&
            isset($_POST['name']) &&
            isset($_POST['id_qualtricsSurveys']) &&
            isset($_POST['id_qualtricsProjectStageTriggerTypes']) &&
            isset($pid)
        ) {
            //insert mode
            $this->insert_stage($pid, $_POST);
        } else if (
            isset($_POST['mode']) && $_POST['mode'] === UPDATE &&
            isset($_POST['id_qualtricsProjectStageTypes']) &&
            isset($_POST['name']) &&
            isset($_POST['id_qualtricsSurveys']) &&
            isset($_POST['id_qualtricsProjectStageTriggerTypes']) &&
            isset($pid)
        ) {
            //edit mode
            $this->update_stage($pid, $_POST);
        } else if (isset($_POST['mode']) && $_POST['mode'] === DELETE && isset($_POST['deleteStageName']) && isset($_POST['deleteStageId'])) {
            //delete mode
            $this->delete_stage($_POST);
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Check the acl for the current user and the current page
     * @retval bool
     * true if access is granted, false otherwise.
     */
    private function check_acl($mode)
    {
        if (!$this->model->get_services()->get_acl()->has_access($_SESSION['id_user'], $this->model->get_services()->get_db()->fetch_page_id_by_keyword("moduleQualtricsProjectStage"), $mode)) {
            $this->fail = true;
            $this->error_msgs[] = "You dont have rights to " . $mode . " this stage";
            return false;
        } else {
            return true;
        }
    }

    /**
     * Create new stage for project and survey
     * @param int $pid
     * project id
     * @param array $data
     * id_qualtricsProjectStageTypes,
     * name,
     * id_qualtricsSurveys,
     * id_qualtricsProjectStageTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     */
    private function insert_stage($pid, $data)
    {
        $this->pid = $this->model->insert_new_stage($pid, $data);
        if ($this->pid > 0) {
            $this->success = true;
            $this->success_msgs[] = "Stage " . $data['name'] . " was successfully added";
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to add a new stage to the project.";
        }
    }

    /**
     * Delete qualtrics stage
     * @param array $data
     * deleteStageId,
     * deleteStageName
     */
    private function delete_stage($data)
    {
        $selectedStage = $this->model->get_db()->select_by_uid("view_qualtricsStages", $data['deleteStageId']);
        if ($selectedStage['stage_name'] === $data['deleteStageName']) {
            $res = $this->model->get_db()->remove_by_fk("qualtricsStages", "id", $selectedStage['id']);
            if ($res) {
                $this->mode = "deleted";
                $this->success = true;
                $this->success_msgs[] = "Stage " . $selectedStage['stage_name'] . " was successfully deleted";
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Failed to delete the stage.";
            }
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to delete the stage: The verification text does not match with the stage name.";
        }
    }

    /**
     * Update qualtrics stage
     * @param int $pid
     * project id
     * @param array $data
     *  id_qualtricsProjectStageTypes,
     * name,
     * id_qualtricsSurveys,
     * id_qualtricsProjectStageTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     */
    private function update_stage($pid, $data)
    {
        if ($this->model->update_stage($pid, $data) !== false) {
            $this->success = true;
            $this->success_msgs[] = "Stage " . $data['name'] . " was successfully updated";
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to update the stage";
        }
    }

    /* Public Methods *********************************************************/
}
?>
