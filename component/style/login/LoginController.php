<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the login component. Note that this class performs
 * a page redirect upon successful login.
 */
class LoginController extends BaseController
{
    /* Private Properties *****************************************************/

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
        if($model->is_logged_in()) $model->logout();

        $this->failed = false;

        if(isset($_POST['email']) && isset($_POST['password']))
        {
            if($model->check_login_credentials($_POST['email'], $_POST['password']))
                header('Location: ' . $model->get_link_url("home"));
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
