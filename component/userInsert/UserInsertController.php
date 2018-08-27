<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the user insert component.
 */
class UserInsertController extends BaseController
{
    /* Private Properties *****************************************************/

    private $success;
    private $fail;
    private $uid;
    private $email;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the cms component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->success = false;
        $this->fail = false;
        $this->uid = null;
        $this->email = "";
        if(isset($_POST['email']))
        {
            $this->email = $_POST['email'];
            $this->insert_new_user($this->email);
        }
    }

    /* Private Methods ********************************************************/

    private function insert_new_user($email)
    {
        $groups = array();
        if(isset($_POST['user_groups'])) $groups = $_POST['user_groups'];
        $this->uid = $this->model->insert_new_user($email);
        if($this->uid && $this->model->add_groups_to_user($this->uid, $groups))
            $this->success = true;
        else
            $this->fail = true;
    }

    /* Public Methods *********************************************************/

    public function has_succeeded()
    {
        return $this->success;
    }

    public function has_failed()
    {
        return $this->fail;
    }

    public function get_new_uid()
    {
        return $this->uid;
    }

    public function get_new_email()
    {
        return $this->email;
    }

}
?>
