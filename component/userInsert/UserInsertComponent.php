<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../user/UserModel.php";
require_once __DIR__ . "/UserInsertView.php";
require_once __DIR__ . "/UserInsertController.php";

/**
 * The user insert component.
 */
class UserInsertComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the UserModel class,
     * the UserInsertView class, and the UserInsertController class and passes
     * the view and controller instances to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new UserModel($services, null);
        $controller = new UserInsertController($model);
        $view = new UserInsertView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
