<?php
require_once __DIR__ . "/../../BaseController.php";

/**
 * The controller class of the ResetPasswordComponent.
 */
class ResetPasswordController extends BaseController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  An instance of the class ResetPasswordModel.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(isset($_POST['phone7h92jP']) && trim($_POST['phone7h92jP']) != "")
        {
            // Probably a bot
            $this->success = true;
            return;
        }
        if(isset($_POST['email']))
        {
            if($this->model->user_set_new_token(filter_var($_POST['email'],
                FILTER_SANITIZE_EMAIL)))
                $this->success = true;
            else
                $this->fail = true;
        }
    }

    /* Public Methods *********************************************************/
}
?>
