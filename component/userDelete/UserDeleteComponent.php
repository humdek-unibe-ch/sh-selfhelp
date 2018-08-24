<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../user/UserModel.php";
require_once __DIR__ . "/UserDeleteView.php";
require_once __DIR__ . "/UserDeleteController.php";

/**
 * The user delete component.
 */
class UserDeleteComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the UserModel class, the
     * UserDeleteView class, and the UserDeleteController class and passes the
     * view instance to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $params)
    {
        $model = new UserModel($services, $params['uid']);
        $controller = new UserDeleteController($model);
        $view = new UserDeleteView($model, $controller);
        parent::__construct($view, $controller);
    }
}
?>
