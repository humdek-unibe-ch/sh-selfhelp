<?php
require_once __DIR__ . "/../user/UserComponent.php";
require_once __DIR__ . "/../user/UserModel.php";
require_once __DIR__ . "/UserSelectView.php";

/**
 * The user component.
 */
class UserSelectComponent extends UserComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CmsModel class, the UserView
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $params)
    {
        $uid = null;
        if(isset($params['uid'])) $uid = intval($params['uid']);
        $model = new UserModel($services, $uid);
        $view = new UserSelectView($model);
        parent::__construct($model, $view);
    }
}
?>
