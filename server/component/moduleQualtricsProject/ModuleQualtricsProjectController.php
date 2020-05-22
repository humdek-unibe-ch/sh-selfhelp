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
class ModuleQualtricsProjectController extends BaseController
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
        $this->model->send_mail();
        if (isset($_POST['mode']) && !$this->check_acl($_POST['mode'])){
            return false;
        }
        if (isset($_POST['mode']) && $_POST['mode'] === INSERT && isset($_POST['name'])) {
            //insert mode
            $this->insert_project($_POST);
        } else if (isset($_POST['mode']) && $_POST['mode'] === UPDATE && isset($_POST['name']) && isset($_POST['id'])) {
            //edit mode
            $this->update_project($_POST);
        } else if (isset($_POST['mode']) && $_POST['mode'] === DELETE && isset($_POST['deleteProjectName']) && isset($_POST['deleteProjectId'])) {
            //delete mode
            $this->delete_project($_POST);
        }
    }

    /* Private Methods ********************************************************/

    /**
     * Check the acl for the current user and the current page
     * @retval bool
     * true if access is granted, false otherwise.
     */
    private function check_acl($mode){
        if(!$this->model->get_services()->get_acl()->has_access($_SESSION['id_user'], $this->model->get_services()->get_db()->fetch_page_id_by_keyword("moduleQualtricsProject"), $mode)){
            $this->fail = true;
            $this->error_msgs[] = "You dont have rights to " . $mode . " this project";
            return false;
        }else{
            return true;
        }
    }

    /**
     * Create new qualtrics project
     * @param array $data
     * name,
     * description,
     * api_mailing_group_id
     */
    private function insert_project($data)
    {
        $this->pid = $this->model->insert_new_project($data);
        if ($this->pid > 0) {
            $this->success = true;
            $this->success_msgs[] = "Project " . $data['name'] . " was successfully created";
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to create a new project.";
        }
    }

    /**
     * Update qualtrics project
     * @param array $data
     * id,
     * name,
     * description,
     * api_mailing_group_id
     */
    private function update_project($data)
    {
        if ($this->model->update_project($data) !== false) {
            $this->success = true;
            $this->success_msgs[] = "Project " . $data['name'] . " was successfully updated";
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to update the project";
        }
    }

    /**
     * Delete qualtrics project
     * @param array $data
     * deleteProjectId,
     * deleteProjectName
     */
    private function delete_project($data)
    {
        $selectedProject = $this->model->get_db()->select_by_uid("qualtricsProjects", $data['deleteProjectId']);
        if ($selectedProject['name'] === $data['deleteProjectName']) {
            $res = $this->model->get_db()->remove_by_fk("qualtricsProjects", "id", $selectedProject['id']);
            if ($res) {
                $this->mode = "deleted";
                $this->success = true;
                $this->success_msgs[] = "Project " . $selectedProject['name'] . " was successfully deleted";
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Failed to delete the project.";
            }
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to delete the project: The verification text does not match with the project name.";
        }
    }

    /* Public Methods *********************************************************/
}
?>
