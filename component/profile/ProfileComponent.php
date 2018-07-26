<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/ProfileView.php";
require_once __DIR__ . "/ProfileModel.php";
require_once __DIR__ . "/ProfileController.php";

/**
 * The user profile component.
 *
 * Similar to the login component, the profile component has a very basic model
 * but needs a custom controller to handle the input data.
 */
class ProfileComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the ProfileModel class, the
     * ProfileView class, and the ProfileController class and passes the view
     * instance to the constructor of the parent class.
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
        $model = new ProfileModel($router, $db);
        $controller = new ProfileController($login);
        $view = new ProfileView($model, $controller);
        parent::__construct($view);
    }
}
?>
