<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the user profile component.
 */
class ProfileController extends BaseController
{
    /* Private Properties *****************************************************/

    private $success_change;
    private $fail_change;
    private $success_delete;
    private $fail_delete;

    /* Constructors ***********************************************************/

    /**
     * The constructor submits the new password data to the database to update
     * the password.
     *
     * @param object $login
     *  The login class that allows to check user credentials.
     */
    public function __construct($login)
    {
        parent::__construct();
        $this->success_change = false;
        $this->fail_change = false;
        $this->success_delete = false;
        $this->fail_delte = false;

        if(isset($_POST['email']))
        {
            $res = $login->delete_user($_POST['email']);
            if($res) $login->logout();
            $this->success_delete = $res;
            $this->fail_delete = !$res;
        }

        if(isset($_POST['password']) && isset($_POST['verification']))
        {
            $res = $login->change_password($_POST['password'],
                $_POST['verification']);
            $this->success_change = $res;
            $this->fail_change = !$res;
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Returns the failure status of the user removal process.
     *
     * @retval bool
     *  true if the user removal has failed, false otherwise.
     */
    public function has_delete_failed()
    {
        return $this->fail_delete;
    }

    /**
     * Returns the success status of the user removal process.
     *
     * @retval bool
     *  true if the user removal has succeeded, false otherwise.
     */
    public function has_delete_succeeded()
    {
        return $this->success_delete;
    }

    /**
     * Returns the failure status of the password change process.
     *
     * @retval bool
     *  true if the password change has failed, false otherwise.
     */
    public function has_pw_change_failed()
    {
        return $this->fail_change;
    }

    /**
     * Returns the success status of the password change process.
     *
     * @retval bool
     *  true if the password change has succeeded, false otherwise.
     */
    public function has_pw_change_succeeded()
    {
        return $this->success_change;
    }
}
?>
