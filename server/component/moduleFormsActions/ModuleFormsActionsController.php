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
class ModuleFormsActionsController extends BaseController
{
    /* Private Properties *****************************************************/


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if (isset($_POST['mode']) && !$this->check_acl($_POST['mode'])) {
            return false;
        }
        if (
            isset($_POST['mode']) && $_POST['mode'] === INSERT &&
            isset($_POST['name']) &&
            isset($_POST['id_forms']) &&
            isset($_POST['id_formProjectActionTriggerTypes']) &&
            isset($_POST['id_formActionScheduleTypes']) &&
            isset($_POST['schedule_info'])
        ) {
            //insert mode
            $this->insert_action($_POST);
        } else if (
            isset($_POST['mode']) && $_POST['mode'] === UPDATE &&
            isset($_POST['name']) &&
            isset($_POST['id_forms']) &&
            isset($_POST['id_formProjectActionTriggerTypes']) &&
            isset($_POST['id_formActionScheduleTypes']) &&
            isset($_POST['schedule_info'])
        ) {
            //edit mode
            $this->update_action($_POST);
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
        if (!$this->model->get_services()->get_acl()->has_access($_SESSION['id_user'], $this->model->get_services()->get_db()->fetch_page_id_by_keyword("moduleFormsAction"), $mode)) {
            $this->fail = true;
            $this->error_msgs[] = "You dont have rights to " . $mode . " this action";
            return false;
        } else {
            return true;
        }
    }

    /**
     * Create new action for form
     * @param array $data
     * name,
     * id_forms,
     * id_formProjectActionTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     */
    private function insert_action($data)
    {
        $this->pid = $this->model->insert_new_action($data);
        if ($this->pid > 0) {
            $this->success = true;
            $this->success_msgs[] = "Action " . $data['name'] . " was successfully added";
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to add a new action to the project.";
        }
    }

    /**
     * Delete form action
     * @param array $data
     * deleteActionId,
     * deleteActionName
     */
    private function delete_action($data)
    {
        $selectedAction = $this->model->get_services()->get_db()->select_by_uid("view_formActions", $data['deleteActionId']);
        if ($selectedAction['action_name'] === $data['deleteActionName']) {
            $res = $this->model->get_services()->get_db()->remove_by_fk("formActions", "id", $selectedAction['id']);
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
     * Update form action     
     * @param array $data
     * name,
     * id_forms,
     * id_formProjectActionTriggerTypes,
     * id_groups array,
     * notification array,
     * reminder array,
     * id_functions array
     */
    private function update_action($data)
    {
        if ($this->model->update_action($data) !== false) {
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
