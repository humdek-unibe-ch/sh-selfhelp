<?php
require_once __DIR__ . "/../user/BaseUserComponent.php";
require_once __DIR__ . "/../user/UserModel.php";
require_once __DIR__ . "/UserUpdateView.php";
require_once __DIR__ . "/UserUpdateController.php";

/**
 * The user update component.
 */
class UserUpdateComponent extends BaseUserComponent
{
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
        $did = null;
        if(isset($params['did'])) $did = $params['did'];
        $model = new UserModel($services, $params['uid'], $did);
        $controller = new UserUpdateController($model, $params['mode']);
        $view = new UserUpdateView($model, $controller, $params['mode']);
        parent::__construct($model, $view, $controller);
    }
}
?>
