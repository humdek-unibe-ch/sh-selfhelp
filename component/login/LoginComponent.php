<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/LoginView.php";
require_once __DIR__ . "/LoginModel.php";
require_once __DIR__ . "/LoginController.php";

/**
 * The login component.
 */
class LoginComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the LoginModel class and the
     * LoginView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param object $login
     *  The login class that allows to check user credentials.
     */
    public function __construct($router, $db, $login)
    {
        $model = new LoginModel($db);
        $controller = new LoginController($router, $login);
        $view = new LoginView($router, $model, $controller);
        parent::__construct($view);
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes()
    {
        return array(
            __DIR__ . "/login.css"
        );
    }
}
?>
