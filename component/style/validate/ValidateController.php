<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the validate component.
 */
class ValidateController extends BaseController
{
    /* Private Properties *****************************************************/

    private $success;
    private $fail;

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
        if(isset($_POST['name']) && isset($_POST['pw'])
            && isset($_POST['pw_verify']) && isset($_POST['gender']))
        {
            if($_POST['pw'] === $_POST['pw_verify']
                && $this->model->activate_user(
                    filter_var($_POST['name'], FILTER_SANITIZE_STRING),
                    password_hash($_POST['pw'], PASSWORD_DEFAULT),
                    filter_var($_POST['gender'], FILTER_SANITIZE_NUMBER_INT)))
                $this->success = true;
            else
                $this->fail = true;
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Returns the failure status of
     *
     * @retval bool
     *  true if the operation has failed, false otherwise.
     */
    public function has_failed()
    {
        return $this->fail;
    }

    /**
     * Returns the success status of
     *
     * @retval bool
     *  true if the operation has succeeded, false otherwise.
     */
    public function has_succeeded()
    {
        return $this->success;
    }
}
?>
