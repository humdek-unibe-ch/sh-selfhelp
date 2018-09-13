<?php
require_once __DIR__ . "/../group/GroupModel.php";
require_once __DIR__ . "/../group/GroupComponent.php";
require_once __DIR__ . "/GroupDeleteView.php";
require_once __DIR__ . "/GroupDeleteController.php";

/**
 * The group delete component.
 */
class GroupDeleteComponent extends GroupComponent
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
     *   'gid':     The id of the group to be deleted.
     */
    public function __construct($services, $params)
    {
        $model = new GroupModel($services, intval($params['gid']));
        $controller = new GroupDeleteController($model);
        $view = new GroupDeleteView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
