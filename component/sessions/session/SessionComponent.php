<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/SessionView.php";
require_once __DIR__ . "/SessionModel.php";
require_once __DIR__ . "/../../navSection/NavSectionComponent.php";

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
     * @param int $id
     *  The section id of this session.
     */
    public function __construct($router, $db, $id)
    {
        $this->nav = new NavSectionComponent($router, $db, "session-navigation", $id);
        $model = new SessionModel($router, $db, $id);
        $view = new SessionView($model, $this->nav);
        parent::__construct($view);
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        return parent::get_css_includes($this->nav->get_css_includes());
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        return parent::get_js_includes($this->nav->get_js_includes());
    }
}
?>
