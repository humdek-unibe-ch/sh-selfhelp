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
class ModuleQualtricsSurveyController extends BaseController
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
        if (isset($_POST['mode']) && $_POST['mode'] === INSERT && isset($_POST['name'])) {
            //insert mode
            if (isset($_POST['config']) && $_POST['config'] != '') {
                //vlaidate JSON
                json_decode($_POST['config'], true);
                if (!(json_last_error() === JSON_ERROR_NONE)) {
                    $this->fail = true;
                    $this->error_msgs[] = "Config value is not a valid JSON";
                    return false;
                }
            }
            $this->insert_survey($_POST);
        } else if (isset($_POST['mode']) && $_POST['mode'] === UPDATE && isset($_POST['name']) && isset($_POST['id'])) {
            //edit mode
            if (isset($_POST['config']) && $_POST['config'] != '') {
                //vlaidate JSON
                json_decode($_POST['config'], true);
                if (!(json_last_error() === JSON_ERROR_NONE)) {
                    $this->fail = true;
                    $this->error_msgs[] = "Config value is not a valid JSON";
                    return false;
                }
            }
            $this->update_survey($_POST);
        } else if (isset($_POST['mode']) && $_POST['mode'] === DELETE && isset($_POST['deleteSurveyName']) && isset($_POST['deleteSurveyId'])) {
            //delete mode
            $this->delete_survey($_POST);
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
        if (!$this->model->get_services()->get_acl()->has_access($_SESSION['id_user'], $this->model->get_services()->get_db()->fetch_page_id_by_keyword("moduleQualtricsSurvey"), $mode)) {
            $this->fail = true;
            $this->error_msgs[] = "You dont have rights to " . $mode . " this survey";
            return false;
        } else {
            return true;
        }
    }

    /**
     * Create new qualtrics survey
     * @param array $data
     * name,
     * description,
     * api_mailing_group_id
     */
    private function insert_survey($data)
    {
        $this->pid = $this->model->insert_new_survey($data);
        if ($this->pid > 0) {
            $this->success = true;
            $this->success_msgs[] = "Survey " . $data['name'] . " was successfully created";
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to create a new survey.";
        }
    }

    /**
     * Update qualtrics survey
     * @param array $data
     * id,
     * name,
     * description,
     * api_mailing_group_id
     */
    private function update_survey($data)
    {
        if ($this->model->update_survey($data) !== false) {
            $this->success = true;
            $this->success_msgs[] = "Survey " . $data['name'] . " was successfully updated";
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to update the survey";
        }
    }

    /**
     * Delete qualtrics survey
     * @param array $data
     * deleteSurveyId,
     * deleteSurveyName
     */
    private function delete_survey($data)
    {
        $selectedSurvey = $this->model->get_db()->select_by_uid("qualtricsSurveys", $data['deleteSurveyId']);
        if ($selectedSurvey['name'] === $data['deleteSurveyName']) {
            $res = $this->model->get_db()->remove_by_fk("qualtricsSurveys", "id", $selectedSurvey['id']);
            if ($res) {
                $this->mode = "deleted";
                $this->success = true;
                $this->success_msgs[] = "Survey " . $selectedSurvey['name'] . " was successfully deleted";
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Failed to delete the survey.";
            }
        } else {
            $this->fail = true;
            $this->error_msgs[] = "Failed to delete the survey: The verification text does not match with the survey name.";
        }
    }

    /* Public Methods *********************************************************/
}
?>
