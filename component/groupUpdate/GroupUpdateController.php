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
     *  The model instance of the cms component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(isset($_POST['update_acl']))
        {
            $this->model->init_acl_table();
            $lvls = array("select", "insert", "update", "delete");
            foreach($lvls as $lvl)
            {
                if(isset($_POST["core"][$lvl]))
                    $this->model->set_core_access($lvl);
                if(isset($_POST["experiment"][$lvl]))
                    $this->model->set_experiment_access($lvl);
                if(isset($_POST["user"][$lvl]))
                    $this->model->set_user_access($lvl);
                if(isset($_POST["page"][$lvl]))
                    $this->model->set_page_access($lvl);
            }
            if($this->model->dump_acl_table())
                $this->success = true;
            else
                $this->fail = true;
        }
    }
}
?>
