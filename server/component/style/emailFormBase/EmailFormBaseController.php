
<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The base controller class of emailForm style components.
 */
class EmailFormBaseController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'alert_success' (empty string).
     * The allert message to be shown if the content was updated successfully.
     */
    protected $alert_success;

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
        if(isset($_POST['phone7h92jP']) && trim($_POST['phone7h92jP']) != "")
            return; // Probably a bot
        if(!isset($_POST['email_user']))
            return;

        $mail = $_POST['email_user'];
        $this->alert_success = $model->get_db_field("alert_success");

        if(filter_var($mail, FILTER_VALIDATE_EMAIL))
        {
            if($model->perform_email_actions($mail))
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
