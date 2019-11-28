<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../chatAdmin/ChatAdminComponent.php";
require_once __DIR__ . "/../chatAdmin/ChatAdminModel.php";
require_once __DIR__ . "/ChatAdminUpdateView.php";
require_once __DIR__ . "/ChatAdminUpdateController.php";

/**
 * The chat admin update component.
 */
class ChatAdminUpdateComponent extends ChatAdminComponent
{
    /* Private Properties *****************************************************/

    /**
     * The user id to delete.
     */
    private $did;

    /**
     * The update mode of the user. This must be one of the following values:
     *  'add_user':   Add a group to the user.
     *  'rm_user':    Remove a group from a user.
     */
    private $mode;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the UserModel class, the
     * UserUpdateView class, and the UserControllerController class and passes
     * the instances to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'rid':     The room id to modify
     *   'did':     See ChatAdminUpdateComponent::did
     *   'model':   See ChatAdminUpdateComponent::mode
     */
    public function __construct($services, $params)
    {
        $rid = isset($params['rid']) ? intval($params['rid']) : null;
        $this->did = isset($params['did']) ? intval($params['did']) : null;
        $this->mode = isset($params['mode']) ? $params['mode'] : null;

        $model = new ChatAdminModel($services, $rid);
        $controller = new ChatAdminUpdateController($model, $this->mode);
        $view = new ChatAdminUpdateView($model, $controller, $this->mode,
            $this->did);
        parent::__construct($model, $view, $controller);
    }

    /**
     * Redefine the parent function. Check for a correct mode and user id.
     *
     * @retval bool
     *  True if the user exists, false otherwise
     */
    public function has_access()
    {
        if(!in_array($this->mode, array("add_user", "rm_user")))
            return false;
        if($this->did != null && !$this->controller->has_succeeded()
            && !$this->controller->has_failed())
        {
            $users = $this->model->get_active_room_users();
            $is_user_in_room = false;
            foreach($users as $user)
                if($user['id'] == $this->did)
                {
                    $is_user_in_room = true;
                    break;
                }
            return $is_user_in_room;
        }
        return parent::has_access();
    }
}
?>
