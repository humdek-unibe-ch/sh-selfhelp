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

    /**
     * Alert status
     */
    private $failed;

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
        $this->login();
    }

    /* Private Methods *******************************************************/

    /**
     * Handle mobile device registration or redirect user
     * 
     * @param string $redirectUrl URL to redirect to for non-mobile requests
     */
    private function handleMobileOrRedirect($redirectUrl = null)
    {
        if (isset($_POST['mobile']) && $_POST['mobile']) {
            // Set device id for the user
            $device_token = isset($_POST['device_token']) ? $_POST['device_token'] : 'web';
            $device_id = isset($_POST['device_id']) ? $_POST['device_id'] : 'web';
            return $this->model->set_device_id_and_token($device_id, $device_token);
        } else if ($redirectUrl) {
            header('Location: ' . $redirectUrl);
        }
    }

    /**
     * Login the user
     */
    private function login() {
        if($this->model->is_logged_in()) $this->model->logout();

        $this->failed = false;

        if ($this->model->is_anonymous_users()) {
            if (isset($_POST['type']) && $_POST['type'] == 'login' && isset($_POST['user_name']) && isset($_POST['password'])) {
                if ($this->model->check_login_credentials_user_name($_POST['user_name'], $_POST['password'])) {
                    $this->handleMobileOrRedirect($this->model->get_target_url());
                } else {
                    $this->failed = true;
                }
            }
        } else {
            if (isset($_POST['type']) && $_POST['type'] == 'login' && isset($_POST['email']) && isset($_POST['password'])) {
                $res = $this->model->check_login_credentials($_POST['email'], $_POST['password']);
                if ($res === '2fa') {
                    $this->handleMobileOrRedirect($this->model->get_link_url(SH_TWO_FACTOR_AUTHENTICATION));
                } else if ($res) {
                    $this->handleMobileOrRedirect($this->model->get_target_url());
                } else {
                    $this->failed = true;
                }
            }
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
