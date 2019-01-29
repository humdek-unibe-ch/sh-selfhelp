<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the user profile component.
 */
class ProfileController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * The success flag for password changes.
     */
    private $success_change = false;

    /**
     * The fail flag for password changes.
     */
    private $fail_change = false;

    /**
     * The success flag for deliting an account.
     */
    private $success_delete = false;

    /**
     * The fail flag for deliting an account.
     */
    private $fail_delete = false;

    /* Constructors ***********************************************************/

    /**
     * The constructor submits the new password data to the database to update
     * the password.
     *
     * @param object $model
     *  The model instance of the user profile component.
     */
    public function __construct($model)
    {
        parent::__construct($model);

        if(isset($_POST['email']))
        {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $res = $model->delete_user($email);
            $this->success_delete = $res;
            $this->fail_delete = !$res;
        }

        if(isset($_POST['user_name']))
        {
            $name = filter_var($_POST['user_name'], FILTER_SANITIZE_STRING);
            $res = $model->change_user_name($name);
            $this->success_change = $res;
            $this->fail_change = !$res;
        }

        if(isset($_POST['password']) && isset($_POST['verification']))
        {
            $res = $model->change_password(
                filter_var($_POST['password'], FILTER_SANITIZE_STRING),
                filter_var($_POST['verification'], FILTER_SANITIZE_STRING)
            );
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
    public function has_change_failed()
    {
        return $this->fail_change;
    }

    /**
     * Returns the success status of the password change process.
     *
     * @retval bool
     *  true if the password change has succeeded, false otherwise.
     */
    public function has_change_succeeded()
    {
        return $this->success_change;
    }
}
?>
