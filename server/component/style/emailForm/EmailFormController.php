<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of emailForm style component.
 */
class EmailFormController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'alert_success' (empty string).
     * The allert message to be shown if the content was updated successfully.
     */
    private $alert_success;

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

        $mail = $_POST['email_intersted_user'];
        $this->alert_success = $model->get_db_field("alert_success");

        if(filter_var($mail, FILTER_VALIDATE_EMAIL))
        {
            $res = $model->add_email($mail);
            if($res)
                $res = $model->send_emails($mail);
            if($res)
            {
                $this->success = true;
                if($this->alert_success !== "")
                    $this->success_msgs[] = $this->alert_success;
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "An unexpected problem occurred. Please Contact the Server Administrator.";
            }
        }
        else
        {
            $this->fail = true;
            $this->error_msgs[] = "The email address is invalid.";
        }
    }

    /* Private Methods ********************************************************/
}
?>
