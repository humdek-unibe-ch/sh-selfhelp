<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/SessionView.php";
require_once __DIR__ . "/SessionModel.php";
require_once __DIR__ . "/../sessionsNav/SessionsNavComponent.php";

/**
 * A component to for a single, generic session
 */
class SessionComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    private $nav;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the SessionModel class and the
     * SessionView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($router, $db, $id)
    {
        $this->nav = new SessionsNavComponent($router, $db, $id);
        $model = new SessionModel($db, $id);
        $view = new SessionView($router, $model, $this->nav);
        parent::__construct($view);
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
        return $this->nav->get_css_includes();
    }
}
?>
