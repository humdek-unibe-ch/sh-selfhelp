<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/SessionsView.php";
require_once __DIR__ . "/SessionsModel.php";

/**
 * A component to provide an overview of the available sessions.
 *
 * This component uses the navSection component as a content element which is
 * not a simple style but has its own model. Therefore it is necessary to create
 * a custom sessions component that can propagate the necessary information.
 *
 * Note that it would also be possible to not instantiate the nav component in
 * this class here but instantiate the nav model in the SessionsModel class and
 * the NavView in the SessionsView class.
 */
class SessionsComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    private $nav;
    private $view;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the SessionsModel class and the
     * SessionsView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new SessionsModel($services);
        $this->view = new SessionsView($model);
        parent::__construct($this->view);
    }

    public function get_view()
    {
        return $this->view;
    }
}
?>
