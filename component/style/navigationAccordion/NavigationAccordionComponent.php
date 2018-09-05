<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/NavigationAccordionView.php";
require_once __DIR__ . "/NavigationAccordionModel.php";

/**
 * A component for a navigation warpper, using an accordion list.
 */
class NavigationAccordionComponent extends BaseComponent
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
     * @param int $id_active
     *  The id of the curently selected navigation section.
     */
    public function __construct($services, $id, $id_active)
    {
        $model = new NavigationAccordionModel($services, $id, $id_active);
        $view = new NavigationAccordionView($model);
        parent::__construct($view);
    }
}
?>
