<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/NavView.php";
require_once __DIR__ . "/NavModel.php";

/**
 * The class to define the navigation component. This component has a
 * non-standard model which constructs the hierarchical menu structure from the
 * pages database table.
 */
class NavComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the NavModel class and the
     * NavView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new NavModel($services);
        $view = new NavView($model);
        parent::__construct($view);
    }
}
?>
