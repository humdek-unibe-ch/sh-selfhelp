<?php
require_once __DIR__ . "/../user/UserController.php";
/**
 * The controller class of the user insert component.
 */
class UserInsertController extends UserController
{
    /* Private Properties *****************************************************/

    private $uid;
    private $email;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->uid = null;
        $this->email = "";
        if(isset($_POST['email']))
        {
            if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            {
                $this->fail = true;
                return;
            }
            $this->email = $_POST['email'];
            $groups = array();
            if(isset($_POST['user_groups'])) $groups = $_POST['user_groups'];
            $this->uid = $this->model->insert_new_user($_POST['email']);
            if($this->uid && $this->model->add_groups_to_user($this->uid,
                $groups))
                $this->success = true;
            else
                $this->fail = true;
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Return the newly created user id.
     *
     * @return int
     *  The newly created user id.
     */
    public function get_new_uid()
    {
        return $this->uid;
    }

    /**
     * Return the newly created user email.
     *
     * @return int
     *  The newly created user email.
     */
    public function get_new_email()
    {
        return $this->email;
    }
}
?>
