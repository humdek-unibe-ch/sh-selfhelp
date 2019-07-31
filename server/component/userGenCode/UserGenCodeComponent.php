<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../user/UserModel.php";
require_once __DIR__ . "/UserGenCodeView.php";
require_once __DIR__ . "/UserGenCodeController.php";

/**
 * The component to generate validation codes.
 */
class UserGenCodeComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the UserModel class,
     * the UserGenCodeView class, and the UserGenCodeController class and passes
     * the view and controller instances to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new UserModel($services, null);
        $controller = new UserGenCodeController($model);
        $view = new UserGenCodeView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
