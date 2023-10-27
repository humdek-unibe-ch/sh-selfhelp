<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the login component such
 * that the data can easily be displayed in the view of the component.
 */
class LoginModel extends StyleModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     * If a user reaches the login page while already logged in, the user is
     * logged out.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section associated to the profile page.
     * @param array $params
     *  The list of get parameters to propagate.
     */
    public function __construct($services, $id, $params)
    {
        parent::__construct($services, $id, $params);
    }

    /* Public Methods *********************************************************/

    /**
     * A wrapper function for the login service.
     *
     * @retval bool
     *  Returns see Login::is_logged_in()
     */
    public function is_logged_in()
    {
        return $this->login->is_logged_in();
    }

    /**
     * A wrapper function for the method Login::logout of the login service.
     */
    public function logout()
    {
        return $this->login->logout();
    }

    /**
     * A wrapper function for the method Login::get_last_url of the login
     * service.
     */
    public function get_target_url()
    {
        return $this->login->get_target_url($this->router->generate('home'));
    }

    /**
     * A simple wrapper for the credential check in the login service.
     *
     * @param string $email
     *  The email address of the user.
     * @param string $password
     *  The password string entered by the user.
     * @return bool
     *  true if the check was successful, false otherwise.
     */
    public function check_login_credentials($email, $password)
    {
        return $this->login->check_credentials($email, $password);
    }

    /**
     * A simple wrapper for the credential check in the login service based on user_name.
     *
     * @param string $user_name
     *  The user_name of the user.
     * @param string $password
     *  The password string entered by the user.
     * @return bool
     *  true if the check was successful, false otherwise.
     */
    public function check_login_credentials_user_name($user_name, $password)
    {
        return $this->login->check_credentials_user_name($user_name, $password);
    }

    /**
     * Set device_id to the user when logged in from a mobile device
     *
     * @param string $device_id
     *  Unique device_id
     * @retval bool
     *  true if succeded, false otherwise.
     */
    public function set_device_id_and_token($device_id, $device_token)
    {
        return $this->db->update_by_ids('users', array('device_id' => $device_id, 'device_token' => $device_token), array('id' => intval($_SESSION['id_user'])));
    }

    /**
     * Check if the settings are for anonymous_users
     * @return bool
     * Return the result
     */
    public function is_anonymous_users()
    {
        return $this->db->is_anonymous_users();
    }

    /**
     * Check if enable reset password is active for anonymous users
     * @return boolean
     * Return the result of the check
     */
    public function is_reset_password_enabled()
    {
        return $this->db->fetch_page_info(SH_SECURITY_QUESTIONS)['enable_reset_password'];
    }
}
?>
