<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../chatAdmin/ChatAdminController.php";
/**
 * The controller class of the group insert component.
 */
class ChatAdminInsertController extends ChatAdminController
{
    /* Private Properties *****************************************************/

    /**
     * The id of the new chat room.
     */
    private $rid = null;

    /**
     * The name of the new chat room.
     */
    private $name = "";

    /**
     * The description of the new chat room.
     */
    private $description = "";

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
        if(isset($_POST['name']) && isset($_POST['desc']))
        {
            $this->name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $this->description = filter_var($_POST['desc'], FILTER_SANITIZE_STRING);
            $this->rid = $this->model->create_new_room($this->name,
                $this->description);
            if($this->rid)
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to create a new room.";
            }
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Return the newly created chat room id.
     *
     * @return int
     *  The newly created chat room id.
     */
    public function get_new_rid()
    {
        return $this->rid;
    }

    /**
     * Return the newly created chat room name.
     *
     * @return int
     *  The newly created chat room name.
     */
    public function get_new_name()
    {
        return $this->name;
    }
}
?>
