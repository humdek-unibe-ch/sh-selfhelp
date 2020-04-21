<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../user/UserController.php";
/**
 * The controller class of the user delete component.
 */
class UserDeleteController extends UserController
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
                {
                    $this->fail = true;
                    $this->error_msgs[] = "Failed to delete the user.";
                }
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to delete the user: The verification text does not match with the user email.";
            }
        }
    }
}
?>
