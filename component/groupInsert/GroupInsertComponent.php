<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../group/GroupModel.php";
require_once __DIR__ . "/GroupInsertView.php";
require_once __DIR__ . "/GroupInsertController.php";

/**
 * The group insert component.
 */
class GroupInsertComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the GroupModel class,
     * the GroupInsertView class, and the GeoupInsertController class and passes
     * the view and controller instances to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new GroupModel($services, null);
        $controller = new GroupInsertController($model);
        $view = new GroupInsertView($model, $controller);
        parent::__construct($view, $controller);
    }
}
?>
