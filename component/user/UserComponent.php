<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/UserView.php";
require_once __DIR__ . "/UserInsertView.php";
require_once __DIR__ . "/UserModel.php";
require_once __DIR__ . "/UserController.php";

/**
 * The user component.
 */
class UserComponent extends BaseComponent
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
    public function __construct($services, $uid, $mode="select")
    {
        $model = new UserModel($services, $uid);
        $controller = new UserController($model);
        if($mode == "select")
            $view = new UserView($model, $controller);
        else if($mode == "insert")
            $view = new UserInsertView($model, $controller);
        parent::__construct($view);
    }
}
?>
