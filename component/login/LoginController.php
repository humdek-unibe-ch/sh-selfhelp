<?php
/**
 * The controller class of the login component. Note that this class performs
 * a page redirect upon successful login.
 */
class LoginController
{
    /* Private Properties *****************************************************/

    private $failed;

    /* Constructors ***********************************************************/

    /**
     * The constructor does several things:
     * First, if a user is already logged in, the user is logged out.
     * Second, submitted credentials are checked and if successful, the user is
     * redirected to the home page.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $login
     *  The login class that allows to check user credentials.
     */
    public function __construct($router, $login)
    {
        $this->failed = false;
        if($login->is_logged_in())
            $login->logout();

        if(isset($_POST['email']) && isset($_POST['password']))
        {
            if($login->check_credentials($_POST['email'], $_POST['password']))
                header('Location: ' . $router->generate("home"));
            else
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
