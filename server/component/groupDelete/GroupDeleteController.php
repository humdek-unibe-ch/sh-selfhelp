<?php
require_once __DIR__ . "/../group/GroupController.php";
/**
 * The controller class of the group delete component.
 */
class GroupDeleteController extends GroupController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the group delete component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(isset($_POST['name']))
        {
            if($_POST['name'] == $this->selected_group['name'])
            {
                $res = $this->model->delete_group($this->selected_group['id']);
                if($res)
                    $this->success = true;
                else
                {
                    $this->fail = true;
                    $this->error_msgs[] = "Failed to delete the group.";
                }
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to delete the group: The verification text does not match with the group name.";
            }
        }
    }
}
?>
