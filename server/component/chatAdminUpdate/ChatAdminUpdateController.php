<?php
require_once __DIR__ . "/../chatAdmin/ChatAdminController.php";
/**
 * The controller class of the chat admin update component.
 */
class ChatAdminUpdateController extends ChatAdminController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param string $mode
     *  The update mode of the chat room. This must be one of the following
     *  values:
     *   - 'add_user':   Add a user to the chat room
     *   - 'rm_user':    Remove a user from a chat room
     */
    public function __construct($model, $mode)
    {
        parent::__construct($model);
        if($mode == "add_user" && isset($_POST["add_user"]))
        {
            if($this->model->add_user_to_active_room($_POST["add_user"]))
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to add users to the chat room.";
            }
        }
        else if($mode == "rm_user" && isset($_POST["rm_user"]))
        {
            if($this->model->rm_user_from_active_room(intval($_POST["rm_user"])))
            {
                $this->success = true;
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to remove the user from the chat room.";
            }
        }
    }
}
?>
