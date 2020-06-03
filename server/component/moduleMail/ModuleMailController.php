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
class ModuleMailController extends BaseController
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
        if (isset($_POST['dateFrom']) && isset($_POST['dateTo']) && isset($_POST['dateType'])) {
            $this->model->set_date_from($_POST['dateFrom']);
            $this->model->set_date_to($_POST['dateTo']);
            $this->model->set_date_type($_POST['dateType']);
        } else {
            $this->model->set_date_from(date('d-m-Y'));
            $this->model->set_date_to(date('d-m-Y'));
            $this->model->set_date_type('date_to_be_sent');
        }
        if (isset($_POST['mode']) && $this->model->get_mqid() > 0) {
            if (!$this->check_acl(UPDATE)) {
                return false;
            }
            if ($_POST['mode'] === 'delete') {
                //delte logic
                if ($this->model->delete_selected_queue_entry()) {
                    $this->success = true;
                    $this->success_msgs[] = "The mail queue entry was deleted";
                } else {
                    $this->fail = true;
                    $this->error_msgs[] = "The mail queue entry was not deleted";
                }
            } else if ($_POST['mode'] === 'send') {
                //send logic
                if ($this->model->send_selected_queue_entry()) {
                    $this->success = true;
                    $this->success_msgs[] = "The mail queue entry was send";
                } else {
                    $this->fail = true;
                    $this->error_msgs[] = "The mail queue entry was not send";
                }
            }
        } else if (isset($_POST['mode']) && $_POST['mode'] === "run_cron") {
            $this->model->check_queue_and_send();
        } else if (isset($_POST['mode']) && $_POST['mode'] === "composeEmail") {
            if ($this->model->compose_email($_POST)) {
                $this->success = true;
                $this->success_msgs[] = "The mail was scheduled";
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Error! The mail scheduling failed.";
            };
        }
    }

    /**
     * Check the acl for the current user and the current page
     * @retval bool
     * true if access is granted, false otherwise.
     */
    private function check_acl($mode)
    {
        if (!$this->model->get_services()->get_acl()->has_access($_SESSION['id_user'], $this->model->get_services()->get_db()->fetch_page_id_by_keyword("moduleMail"), $mode)) {
            $this->fail = true;
            $this->error_msgs[] = "You dont have rights to send or delete this mail queue";
            return false;
        } else {
            return true;
        }
    }

    /* Public Methods *********************************************************/
}
?>
