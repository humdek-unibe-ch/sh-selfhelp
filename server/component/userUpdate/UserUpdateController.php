<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../user/UserController.php";
/**
 * The controller class of the user update component.
 */
class UserUpdateController extends UserController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param string $mode
     *  The update mode of the user. This must be one of the following values:
     *   'block':       Block a user.
     *   'unblock':     Unblock a user.
     *   'add_group':   Add a group to the user.
     *   'rm_group':    Remove a group from a user.
     */
    public function __construct($model, $mode)
    {
        parent::__construct($model);
        if($mode == "block" && isset($_POST["block"]))
        {
            if($this->model->block_user($this->selected_user['id']))
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to block the user.";
            }
        }
        else if($mode == "unblock" && isset($_POST["unblock"]))
        {
            if($this->model->unblock_user($this->selected_user['id']))
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to unblock the user.";
            }
        }
        else if($mode == "impersonate" && isset($_POST["impersonate"]))
        {
            if($this->model->impersonate_user($this->selected_user['id']))
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to impersonate the user.";
                
            }
        }
        else if($mode == "activation_email" && isset($_POST["activation_email"]))
        {
            if($this->model->send_activation_email($_POST["activation_email"]))
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to send activation email to the user.";
            }
        }
        else if($mode == "clean" && isset($_POST["email"]))
        {
            if($_POST['email'] == $this->selected_user['email'])
            {
                if($this->model->clean_user_data($this->selected_user['id']))
                    $this->success = true;
                else
                {
                    $this->fail = true;
                    $this->error_msgs[] = "Failed to clean the user data.";
                }
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to clean the user data: The verification text does not match with the user email.";
            }
        }
        else if($mode == "add_group" && isset($_POST["groups"]))
        {
            foreach($_POST['groups'] as $group)
            {
                if(!$this->model->is_group_allowed(intval($group)))
                {
                    $this->fail = true;
                    $this->error_msgs[] = "Cannot assign the group to the user: Permission denied.";
                    return;
                }
            }
            if($this->model->add_groups_to_user($this->selected_user['id'],
                $_POST["groups"]))
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to add groups to the user.";
            }
        }
        else if($mode == "rm_group" && isset($_POST["rm_group"]))
        {
            if(!$this->model->is_group_allowed($_POST["rm_group"]))
            {
                $this->fail = true;
                $this->error_msgs[] = "Cannot remove the group from the user: Permission denied.";
                return;
            }
            if($this->model->rm_group_from_user($this->selected_user['id'],
                intval($_POST["rm_group"])))
            {
                $this->success = true;
                $this->model->reset_did();
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to remove the group from the user.";
            }
        }
    }
}
?>
