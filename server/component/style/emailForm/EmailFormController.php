<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of emailForm style component.
 */
class EmailFormController extends BaseController
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(!isset($_POST['email_intersted_user']))
            return;

        $mail = filter_var($_POST['email_intersted_user'], FILTER_SANITIZE_EMAIL);

        if($model->add_email($mail))
            $model->send_emails($mail);
    }

    /* Private Methods ********************************************************/
}
?>
