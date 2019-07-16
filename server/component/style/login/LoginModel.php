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
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);

        $fields = $this->db->fetch_section_fields($id);
        $this->set_db_fields($fields);
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
        return $this->login->get_target_url();
    }

    /**
     * A simple wrapper for the credential check in the login service.
     *
     * @param string $email
     *  The email address of the user.
     * @param string $password
     *  The password string entered by the user.
     * @retval bool
     *  true if the check was successful, false otherwise.
     */
    public function check_login_credentials($email, $password)
    {
        return $this->login->check_credentials($email, $password);
    }
}
?>
