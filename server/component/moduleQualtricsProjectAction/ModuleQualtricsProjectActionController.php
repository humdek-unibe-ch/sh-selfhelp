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
class ModuleQualtricsProjectActionController extends BaseController
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
            isset($_POST['name']) &&
            isset($_POST['id_qualtricsSurveys']) &&
            isset($_POST['id_qualtricsProjectActionTriggerTypes']) &&
            isset($pid)
        ) {
            //insert mode
            $this->insert_action($pid, $_POST);
        } else if (
            isset($_POST['mode']) && $_POST['mode'] === UPDATE &&
            isset($_POST['name']) &&
            isset($_POST['id_qualtricsSurveys']) &&
            isset($_POST['id_qualtricsProjectActionTriggerTypes']) &&
            isset($pid)
        ) {
            //edit mode
            $this->update_action($pid, $_POST);
        } else if (isset($_POST['mode']) && $_POST['mode'] === DELETE && isset($_POST['deleteActionName']) && isset($_POST['deleteActionId'])) {
            //delete mode
            $this->delete_action($_POST);
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
        if (!$this->model->get_services()->get_acl()->has_access($_SESSION['id_user'], $this->model->get_services()->get_db()->fetch_page_id_by_keyword("moduleQualtricsProjectAction"), $mode)) {
            $this->fail = true;
            $this->error_msgs[] = "You dont have rights to " . $mode . " this action";
            return false;
        } else {
            return true;
        }
    }

    /**
     * Create new action for project and survey
     * @param int $pid
     * project id
     * @param array $data
     * id_qualtricsProjectActionTypes,
     * name,
     * id_qualtricsSurveys,
     * id_qualtricsProjectActionTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     */
    private function insert_action($pid, $data)
    {
        $this->pid = $this->model->insert_new_action($pid, $data);
        if ($this->pid > 0) {
            $this->success = true;
            $this->success_msgs[] = "Action " . $data['name'] . " was successfully added";
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to add a new action to the project.";
        }
    }

    /**
     * Delete qualtrics action
     * @param array $data
     * deleteActionId,
     * deleteActionName
     */
    private function delete_action($data)
    {
        $selectedAction = $this->model->get_db()->select_by_uid("view_qualtricsActions", $data['deleteActionId']);
        if ($selectedAction['action_name'] === $data['deleteActionName']) {
            $res = $this->model->get_db()->remove_by_fk("qualtricsActions", "id", $selectedAction['id']);
            if ($res) {
                $this->mode = "deleted";
                $this->success = true;
                $this->success_msgs[] = "Action " . $selectedAction['action_name'] . " was successfully deleted";
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Failed to delete the action.";
            }
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to delete the action: The verification text does not match with the action name.";
        }
    }

    /**
     * Update qualtrics action
     * @param int $pid
     * project id
     * @param array $data
     *  id_qualtricsProjectActionTypes,
     * name,
     * id_qualtricsSurveys,
     * id_qualtricsProjectActionTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     */
    private function update_action($pid, $data)
    {
        if ($this->model->update_action($pid, $data) !== false) {
            $this->success = true;
            $this->success_msgs[] = "Action " . $data['name'] . " was successfully updated";
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to update the action";
        }
    }

    /* Public Methods *********************************************************/
}
?>
