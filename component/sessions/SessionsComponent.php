<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/SessionsView.php";
require_once __DIR__ . "/SessionsModel.php";
require_once __DIR__ . "/../navSection/NavSectionComponent.php";

/**
 * A component to provide an overview of the available sessions.
 */
class SessionsComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    private $nav;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the SessionsModel class and the
     * SessionsView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($router, $db)
    {
        $this->nav = new NavSectionComponent($router, $db, "session-navigation");
        $model = new SessionsModel($router, $db);
        $view = new SessionsView($model, $this->nav);
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
