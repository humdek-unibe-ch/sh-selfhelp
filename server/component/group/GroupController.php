<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The base controller class of the group component.
 */
class GroupController extends BaseController
{
    /* Private Properties *****************************************************/

    protected $success;
    protected $fail;
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
        $this->success = false;
        $this->fail = false;
        $this->selected_group = $this->model->get_selected_group();
    }

    /* Protected Methods ******************************************************/

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

    /* Public Methods *********************************************************/

    /**
     * Returns true if the the operation was successful.
     *
     * @retval bool
     *  True if the operation was successful, false if no successful operation
     *  was performed.
     */
    public function has_succeeded()
    {
        return $this->success;
    }

    /**
     * Returns true if the the operation failed.
     *
     * @retval bool
     *  True if the operation failed, false if no failed operation was
     *  performed.
     */
    public function has_failed()
    {
        return $this->fail;
    }

}
?>
