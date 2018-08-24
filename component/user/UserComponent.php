<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/UserView.php";
require_once __DIR__ . "/UserModel.php";

/**
 * The user component.
 */
class UserComponent extends BaseComponent
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
        if(isset($params['uid'])) $uid = $params['uid'];
        $model = new UserModel($services, $uid);
        $view = new UserView($model);
        parent::__construct($view);
    }
}
?>
