<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the register component.
 */
class RegisterController extends BaseController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor. Submitted credentials are checked.
     *
     * @param object $model
     *  The model instance of the register component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(isset($_POST['email']) && isset($_POST['code']))
        {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $code = filter_var($_POST['code'], FILTER_SANITIZE_STRING);
            if($email !== false && $code !== false
                && $model->register_user($email, $code))
                $this->success = true;
            else
                $this->fail = true;
        }
    }

    /* Public Methods *********************************************************/
}
?>
