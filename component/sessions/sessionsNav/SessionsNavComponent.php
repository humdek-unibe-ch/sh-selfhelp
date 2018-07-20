<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/SessionsNavView.php";
require_once __DIR__ . "/SessionsNavModel.php";

/**
 * A component to provide an overview of the available sessions as a navigation
 * element.
 */
class SessionsNavComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    private $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the SessionsNavModel class and the
     * SessionsNavView class and passes the view instance to the constructor of
     * the parent class.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($router, $db, $active_session_id)
    {
        $this->model = new SessionsNavModel($db, $active_session_id);
        $view = new SessionsNavView($router, $this->model);
        parent::__construct($view);
    }

    /**
     * Gets the number of sessions of the naviagetion.
     *
     * @retval int
     *  The number of sessions.
     */
    public function get_session_count()
    {
        return $this->model->get_session_count();
    }

    /**
     * Gets the next session id given the current id.
     *
     * @retval int
     *  The next sessions id or false if it does not exist.
     */
    public function get_next_session_id()
    {
        return $this->model->get_next_session_id();
    }

    /**
     * Gets the previous session id given the current id.
     *
     * @retval int
     *  The previous sessions id or false if it does not exist.
     */
    public function get_previous_session_id()
    {
        return $this->model->get_previous_session_id();
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes()
    {
        return array(
            __DIR__ . "/sessionsNav.css"
        );
    }
}
?>
