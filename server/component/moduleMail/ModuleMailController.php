<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the group insert component.
 */
class ModuleMailController extends BaseController
{
    /* Private Properties *****************************************************/


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
        // if(isset($_POST['name']) && isset($_POST['desc']))
        // {
        //     $this->name = $_POST['name'];
        //     if(!$this->check_posted_acl())
        //     {
        //         $this->fail = true;
        //         $this->error_msgs[] = "Cannot assign the selected rights: Permission denied.";
        //         return;
        //     }
        //     $this->gid = $this->model->insert_new_group($_POST['name'],
        //         $_POST['desc']);
        //     if($this->gid && $this->update_group_acl($this->gid))
        //         $this->success = true;
        //     else
        //     {
        //         $this->fail = true;
        //         $this->error_msgs[] = "Failed to create a new group.";
        //     }
        // }
    }

    /* Public Methods *********************************************************/

}
?>
