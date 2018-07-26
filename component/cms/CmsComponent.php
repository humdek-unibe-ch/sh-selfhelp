<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/CmsView.php";
require_once __DIR__ . "/CmsModel.php";
require_once __DIR__ . "/CmsController.php";

/**
 * The cms component.
 */
class CmsComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CmsModel class, the CmsView
     * class, and the CmsController class and passes the view instance to the
     * constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new CmsModel($services);
        $controller = new CmsController();
        $view = new CmsView($model, $controller);
        parent::__construct($view);
    }
}
?>
