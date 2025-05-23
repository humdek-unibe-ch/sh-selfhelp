<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     * @param string $group_type
     * The group type that we want to create: group or db_role
     */
    public function __construct($model, $group_type)
    {
        parent::__construct($model);
        $this->gid = null;
        $this->name = "";
        if(isset($_POST['name']) && isset($_POST['desc']))
        {
            $this->name = $_POST['name'];
            if(!$this->check_posted_acl())
            {
                $this->fail = true;
                $this->error_msgs[] = "Cannot assign the selected rights: Permission denied.";
                return;
            }
            $this->gid = $this->model->insert_new_group($_POST['name'],
                $_POST['desc'], $group_type, $_POST['requires_2fa'] ?? 0);
            if($this->gid && $this->update_group_acl($this->gid))
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to create a new group.";
            }
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
