<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../chatAdmin/ChatAdminController.php";
/**
 * The controller class of the chat admin delete component.
 */
class ChatAdminDeleteController extends ChatAdminController
{
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
        if(isset($_POST['name']))
        {
            if($_POST['name'] == $this->model->get_active_room_name())
            {
                $res = $this->model->delete_active_room();
                if($res)
                    $this->success = true;
                else
                {
                    $this->fail = true;
                    $this->error_msgs[] = "Failed to delete the chat room.";
                }
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to delete the chat room: The verification text does not match with the chat room name.";
            }
        }
    }
}
?>
