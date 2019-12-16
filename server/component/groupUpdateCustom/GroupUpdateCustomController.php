<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../group/GroupController.php";
/**
 * The controller class of the group update component.
 */
class GroupUpdateCustomController extends GroupController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the group component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(isset($_POST['update_custom_acl']))
        {
            if(!$this->check_posted_acl())
            {
                $this->fail = true;
                $this->error_msgs[] = "Cannot assign the selected rights: Permission denied.";
                return;
            }
            if($this->update_group_acl_custom())
            {
                $this->success = true;
                $this->success_msgs[] = "Successfully updated the group ACL.";
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to update the group ACL.";
            }
        }
    }
}
?>
