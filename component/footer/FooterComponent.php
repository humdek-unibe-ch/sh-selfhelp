<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/FooterView.php";
require_once __DIR__ . "/FooterModel.php";

/**
 * The footer component. The footer component is similar to the nav component
 * but is simpler as it does not support a hierarchical menu structure.
 */
class FooterComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the FooterModel class and the
     * FooterView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new FooterModel($services);
        $view = new FooterView($model);
        parent::__construct($model, $view);
    }
}
?>
