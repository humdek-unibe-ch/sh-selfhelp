<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the login component such
 * that the data can easily be displayed in the view of the component.
 */
class LoginModel extends BaseModel
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
     */
    public function __construct($services)
    {
        parent::__construct($services);
        if($this->login->is_logged_in())
            $this->login->logout();

        $fields = $this->db->fetch_page_fields("login");
        $this->set_db_fields($fields);
    }

    /* Public Methods *********************************************************/

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
