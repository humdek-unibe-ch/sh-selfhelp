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
class ModuleQualtricsSyncController extends BaseController
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
        if (isset($_POST['mode']) && isset($_POST['type'])) {
            if (!$this->check_acl($_POST['mode'])) {
                $this->fail = true;
                $this->error_msgs[] = "Cannot synchronize this project with Qualtrics. Permission denied.";
                return;
            }
            $this->syncProjectSurveys($pid);
        }
    }

    /**
     * Check the acl for the current user and the current page
     * @retval bool
     * true if access is granted, false otherwise.
     */
    private function check_acl($mode)
    {
        if (!$this->model->get_services()->get_acl()->has_access($_SESSION['id_user'], $this->model->get_services()->get_db()->fetch_page_id_by_keyword("moduleQualtricsSync"), $mode)) {
            $this->fail = true;
            $this->error_msgs[] = "You dont have rights to synchronize this project";
            return false;
        } else {
            return true;
        }
    }

    /**
     * synchronize all surveys which belong to the project with  Qualtrics
     * @param int $pid Project id
     */
    private function syncProjectSurveys($pid)
    {
        foreach ($this->model->get_actions_for_sync($pid) as $action) {
            $res = $this->model->syncSurvey($action);
            if($res['result']){
                $this->success = true;
                $this->success_msgs[] = 'Survey ' .$action['survey_name'] . ': ' . $res['description'];
            }else{
                $this->fail = true;
                $this->error_msgs[] = $res['description'];
            }
        }
    }

    /* Public Methods *********************************************************/
}
?>
