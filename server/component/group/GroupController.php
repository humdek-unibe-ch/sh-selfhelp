<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The base controller class of the group component.
 */
class GroupController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * An array of group properties (see UserModel::fetch_group).
     */
    protected $selected_group;

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
        $this->selected_group = $this->model->get_selected_group();
    }

    /* Protected Methods ******************************************************/

    protected function check_posted_acl()
    {
        $acl_limit = $this->model->get_simple_acl_current_user();
        $lvls = array("select", "insert", "update", "delete");
        foreach($lvls as $lvl)
        {
            if(isset($_POST["core"][$lvl])
                && !$acl_limit["core"]["acl"][$lvl])
                    return false;
            if(isset($_POST["experiment"][$lvl])
                && !$acl_limit["experiment"]["acl"][$lvl])
                    return false;
            if(isset($_POST["user"][$lvl])
                && !$acl_limit["user"]["acl"][$lvl])
                    return false;
            if(isset($_POST["page"][$lvl])
                && !$acl_limit["page"]["acl"][$lvl])
                    return false;
            if(isset($_POST["data"][$lvl])
                && !$acl_limit["data"]["acl"][$lvl])
                    return false;
        }
        return true;
    }

    /**
     * This method updates the group acl entries in the db. To do this it first
     * initialises the acl table of the model instance, then updates the entries
     * in the table and finally, saves the acl entries to the database.
     *
     * @param int $gid
     *  The group id where the acl will be updated. If no id is provided, the
     *  current group id GroupModel::gid is used.
     * @retval bool
     *  True on success, false otherwise.
     */
    protected function update_group_acl($gid = null)
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
            if(isset($_POST["data"][$lvl]))
                $this->model->set_data_access($lvl);
        }
        return $this->model->dump_acl_table($gid);
    }
}
?>
