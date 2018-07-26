<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/LoginView.php";
require_once __DIR__ . "/LoginModel.php";
require_once __DIR__ . "/LoginController.php";

/**
 * The login component.
 *
 * It has a very simple model where page fields are fetched from the database
 * (no sections). What makes this component special is the controller and,
 * consequently, the view that is depending on the controller.
 */
class LoginComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the LoginModel class and the
     * LoginView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new LoginModel($services);
        $controller = new LoginController($model);
        $view = new LoginView($model, $controller);
        parent::__construct($view);
    }
}
?>
