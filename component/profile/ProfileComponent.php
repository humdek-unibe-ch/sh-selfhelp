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
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     */
    public function __construct($services, $id)
    {
        $model = new ProfileModel($services, $id);
        $controller = new ProfileController($model);
        $view = new ProfileView($model, $controller);
        parent::__construct($view);
    }
}
?>
