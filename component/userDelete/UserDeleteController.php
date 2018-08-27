<?php
require_once __DIR__ . "/../user/BaseUserController.php";
/**
 * The controller class of the user delete component.
 */
class UserDeleteController extends BaseUserController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the user delete component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(isset($_POST['email']))
        {
            if($_POST['email'] == $this->selected_user['email'])
            {
                $res = $this->model->delete_user($this->selected_user['id']);
                if($res)
                    $this->success = true;
                else
                    $this->fail = true;
            }
            else
                $this->fail = true;
        }
    }
}
?>
