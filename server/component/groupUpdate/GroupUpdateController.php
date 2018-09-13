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
            if($this->update_group_acl())
                $this->success = true;
            else
                $this->fail = true;
        }
    }
}
?>
