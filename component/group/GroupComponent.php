<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/GroupView.php";
require_once __DIR__ . "/GroupModel.php";
require_once __DIR__ . "/GroupController.php";

/**
 * The group component.
 */
class GroupComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the GroupModel class, the
     * GroupView class, and the GroupController class and passes the view
     * instance to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $uid)
    {
        $model = new GroupModel($services, $uid);
        $controller = new GroupController($model);
        $view = new GroupView($model, $controller);
        parent::__construct($view);
    }
}
?>
