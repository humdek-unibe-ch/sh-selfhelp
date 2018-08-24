<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../user/UserModel.php";
require_once __DIR__ . "/UserUpdateView.php";
require_once __DIR__ . "/UserUpdateController.php";

/**
 * The user update component.
 */
class UserUpdateComponent extends BaseComponent
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
     */
    public function __construct($services, $params)
    {
        $did = null;
        if(isset($params['did'])) $did = $params['did'];
        $model = new UserModel($services, $params['uid'], $did);
        $controller = new UserUpdateController($model, $params['mode']);
        $view = new UserUpdateView($model, $controller, $params['mode']);
        parent::__construct($view);
    }
}
?>
