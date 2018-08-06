<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/SessionView.php";
require_once __DIR__ . "/SessionModel.php";

/**
 * A component to for a single, generic session
 */
class SessionComponent extends BaseComponent
{
    private $view;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the SessionModel class and the
     * SessionView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this session.
     */
    public function __construct($services, $id)
    {
        $model = new SessionModel($services, $id);
        $this->view = new SessionView($model);
        parent::__construct($this->view);
    }

    public function get_view()
    {
        return $this->view;
    }
}
?>
