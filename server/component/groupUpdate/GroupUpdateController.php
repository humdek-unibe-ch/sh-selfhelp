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
        if(isset($_POST['update_acl']))
        {
            if(!$this->check_posted_acl())
            {
                $this->fail = true;
                $this->error_msgs[] = "Cannot assign the selected rights: Permission denied.";
                return;
            }
            if($this->update_group_acl())
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
