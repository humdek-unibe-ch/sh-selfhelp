<?php
require_once __DIR__ . "/../group/GroupController.php";
/**
 * The controller class of the group insert component.
 */
class GroupInsertController extends GroupController
{
    /* Private Properties *****************************************************/

    /**
     * The id of the new group.
     */
    private $gid;

    /**
     * The name of the new group.
     */
    private $name;

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
        $this->gid = null;
        $this->name = "";
        if(isset($_POST['name']) && isset($_POST['desc']))
        {
            $this->name = $_POST['name'];
            $groups = array();
            $this->gid = $this->model->insert_new_group($_POST['name'],
                $_POST['desc']);
            if($this->gid && $this->update_group_acl($this->gid))
                $this->success = true;
            else
                $this->fail = true;
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Return the newly created group id.
     *
     * @return int
     *  The newly created group id.
     */
    public function get_new_gid()
    {
        return $this->gid;
    }

    /**
     * Return the newly created group name.
     *
     * @return int
     *  The newly created group name.
     */
    public function get_new_name()
    {
        return $this->name;
    }
}
?>
