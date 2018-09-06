<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/AccordionListView.php";
require_once __DIR__ . "/AccordionListModel.php";

/**
 * A component for a accordion list component.
 */
class AccordionListComponent extends BaseComponent
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
        $model = new AccordionListModel($services, $id);
        $view = new AccordionListView($model);
        parent::__construct($view);
    }
}
?>
