<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the user update component.
 */
class UserUpdateController extends BaseController
{
    /* Private Properties *****************************************************/

    private $success;
    private $fail;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the cms component.
     */
    public function __construct($model, $mode)
    {
        parent::__construct($model);
        $this->success = false;
        $this->fail = false;
        $this->selected_user = $this->model->get_selected_user();
        if($mode == "block" && isset($_POST["block"]))
        {
            if($this->model->block_user($this->selected_user['id']))
                $this->success = true;
            else
                $this->fail = true;
        }
        else if($mode == "unblock" && isset($_POST["unblock"]))
        {
            if($this->model->unblock_user($this->selected_user['id']))
                $this->success = true;
            else
                $this->fail = true;
        }
        else if($mode == "add_group" && isset($_POST["groups"]))
        {
            if($this->model->add_groups_to_user($this->selected_user['id'],
                $_POST["groups"]))
                $this->success = true;
            else
                $this->fail = true;
        }
        else if($mode == "rm_group" && isset($_POST["rm_group"]))
        {
            if($this->model->rm_group_from_user($this->selected_user['id'],
                intval($_POST["rm_group"])))
                $this->success = true;
            else
                $this->fail = true;
        }
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    public function has_succeeded()
    {
        return $this->success;
    }

    public function has_failed()
    {
        return $this->fail;
    }

}
?>
