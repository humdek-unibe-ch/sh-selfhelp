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
class GroupUpdateController extends GroupController
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
        if(isset($_POST['update_group']))
        {
            if($this->update_group())
            {
                $this->success = true;
                $this->success_msgs[] = "Successfully updated the group.";
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to update the group.";
            }
        }
    }
}
?>
