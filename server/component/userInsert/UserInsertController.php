<?php
require_once __DIR__ . "/../user/UserController.php";
/**
 * The controller class of the user insert component.
 */
class UserInsertController extends UserController
{
    /* Private Properties *****************************************************/

    /**
     * The id of the new user.
     */
    private $uid;

    /**
     * The email of the new user.
     */
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
        if(isset($_POST['email']) && isset($_POST['code']))
        {
            if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            {
                $this->fail = true;
                $this->error_msgs[] = "Invalid email address.";
                return;
            }
            $this->email = $_POST['email'];
            $groups = array();
            if(isset($_POST['user_groups'])) $groups = $_POST['user_groups'];
            foreach($groups as $group)
            {
                if(!$this->model->is_group_allowed(intval($group)))
                {
                    $this->fail = true;
                    $this->error_msgs[] = "Cannot assign the group to the user: Permission denied.";
                    return;
                }
            }
            $this->uid = $this->model->create_new_user($_POST['email'], $_POST['code']);
            if($this->uid && $this->model->add_groups_to_user($this->uid,
                $groups))
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to create a new user.";
            }
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
