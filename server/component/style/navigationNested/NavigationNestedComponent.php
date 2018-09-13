<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/NavigationNestedView.php";
require_once __DIR__ . "/../navigation/NavigationModel.php";

/**
 * A component for a navigation wrapper, using a nested list.
 */
class NavigationNestedComponent extends BaseComponent
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
     * @param array $params
     *  An array of parameters. This component requires the following keys:
     *   'nav': The id of the curently selected navigation section.
     */
    public function __construct($services, $id, $params)
    {
        $id_active = null;
        if(isset($params['nav'])) $id_active = intval($params['nav']);
        $model = new NavigationModel($services, $id, $id_active);
        $view = new NavigationNestedView($model);
        parent::__construct($model, $view);
    }
}
?>
