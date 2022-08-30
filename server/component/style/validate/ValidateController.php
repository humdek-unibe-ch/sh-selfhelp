<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the validate component.
 */
class ValidateController extends BaseController
{

    /* Constructors ***********************************************************/

    /**
     * The constructor
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->success = false;
        $this->fail = false;
        if(isset($_POST['phone7h92jP']) && trim($_POST['phone7h92jP']) != "")
        {
            // Probably a bot
            $this->success = true;
            return;
        }
        if(isset($_POST['name']) && isset($_POST['pw'])
            && isset($_POST['pw_verify']) && isset($_POST['gender']))
        {
            if($_POST['pw'] === $_POST['pw_verify']
                && $this->model->activate_user(
                    filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
                    password_hash($_POST['pw'], PASSWORD_DEFAULT),
                    filter_var($_POST['gender'], FILTER_SANITIZE_NUMBER_INT)))
            {
                $this->success = true;
                unset($_SESSION['target_url']);
                if (isset($_POST['mobile']) && $_POST['mobile']) {
                    $this->success_msgs[] = $this->model->get_db_field('alert_success');
                }
            } else {
                $this->fail = true;
                if (isset($_POST['mobile']) && $_POST['mobile']) {
                    $this->error_msgs[] = $this->model->get_db_field("alert_fail");
                }
            }
        }
    }
}
?>
