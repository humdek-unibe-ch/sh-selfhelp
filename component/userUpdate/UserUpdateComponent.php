<?php
require_once __DIR__ . "/../user/UserComponent.php";
require_once __DIR__ . "/../user/UserModel.php";
require_once __DIR__ . "/UserUpdateView.php";
require_once __DIR__ . "/UserUpdateController.php";

/**
 * The user update component.
 */
class UserUpdateComponent extends UserComponent
{
    /* Private Properties *****************************************************/

    private $did;
    private $mode;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CmsModel class, the CmsView
     * class, and the CmsController class and passes the view instance to the
     * constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'uid':     The id of the user to be deleted.
     *   'mode':    The update mode of the user. This must be one of the
     *              following values:
     *               'block':       Block a user.
     *               'unblock':     Unblock a user.
     *               'add_group':   Add a group to the user.
     *               'rm_group':    Remove a group from a user.
     */
    public function __construct($services, $params)
    {
        if(isset($params['uid'])) $uid = $params['uid'];
        $this->did = null;
        if(isset($params['did'])) $this->did = $params['did'];
        $this->mode = null;
        if(isset($params['mode'])) $this->mode = $params['mode'];
        $model = new UserModel($services, $uid, $this->did);
        $controller = new UserUpdateController($model, $this->mode);
        $this->did = $model->get_did();
        $view = new UserUpdateView($model, $controller, $this->mode);
        parent::__construct($model, $view, $controller);
    }

    /**
     * Redefine the parent function. Check for a correct mode and group id.
     *
     * @retval bool
     *  True if the user exists, false otherwise
     */
    public function has_access()
    {
        if(!in_array($this->mode, array("block", "unblock", "add_group",
            "rm_group")))
            return false;
        if($this->did != null)
        {
            $ugroups = $this->model->get_selected_user_groups();
            $is_user_group = false;
            foreach($ugroups as $group)
                if($group['id'] == $this->did)
                {
                    $is_user_group = true;
                    break;
                }
            if(!$is_user_group)
                return false;
        }
        return parent::has_access();
    }
}
?>
