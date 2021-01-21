<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the login component. Note that this class performs
 * a page redirect upon successful login.
 */
class LoginController extends BaseController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor. Submitted credentials are checked and if successful,
     * the user is redirected to the home page.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if($model->is_logged_in()) $model->logout();

        $this->failed = false;

        if(isset($_POST['type']) && $_POST['type'] == 'login' && isset($_POST['email']) && isset($_POST['password']))
        {
            if($model->check_login_credentials($_POST['email'], $_POST['password'])) {
                if (isset($_POST['mobile']) && $_POST['mobile']) {
                    // set device id for the user
                    $this->model->set_device_id_and_token($_POST['device_id'], $_POST['device_token']);
                }else{
                    header('Location: ' . $model->get_target_url());
                }
            }else
                $this->failed = true;
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Returns the failure status of the login process.
     *
     * @retval bool
     *  true if the login has failed, false otherwise.
     */
    public function has_login_failed()
    {
        return $this->failed;
    }
}
?>
