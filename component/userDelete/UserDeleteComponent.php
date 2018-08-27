<?php
require_once __DIR__ . "/../user/UserModel.php";
require_once __DIR__ . "/../user/BaseUserComponent.php";
require_once __DIR__ . "/UserDeleteView.php";
require_once __DIR__ . "/UserDeleteController.php";

/**
 * The user delete component.
 */
class UserDeleteComponent extends BaseUserComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model, the View, and the
     * Controller and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'uid':     The id of the user to be deleted.
     */
    public function __construct($services, $params)
    {
        $model = new UserModel($services, $params['uid']);
        $controller = new UserDeleteController($model);
        $view = new UserDeleteView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
