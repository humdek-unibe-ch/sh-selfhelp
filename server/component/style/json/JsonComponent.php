<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/JsonModel.php";
require_once __DIR__ . "/JsonView.php";

/**
 * A component class for the json style component.
 * This component allows to describe nested styles as a json string.
 */
class JsonComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this navigation component.
     */
    public function __construct($services, $id)
    {
        $model = new JsonModel($services, $id);
        $view = new JsonView($model);
        parent::__construct($model, $view);
    }
}
?>
