<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/SessionsView.php";
require_once __DIR__ . "/SessionsModel.php";
require_once __DIR__ . "/../navSection/NavSectionComponent.php";

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
    public function __construct($services)
    {
        $sections = $services['db']->fetch_page_sections("sessions");
        $id_nav = 0;
        foreach($sections as $section)
            if(intval($section['id_styles']) == NAVIGATION_STYLE_ID)
            {
                $id_nav = intval($section['id']);
                break;
            }
        $this->nav = new NavSectionComponent($services, $id_nav);
        $model = new SessionsModel($services['router'], $services['db']);
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
    public function get_css_includes($local = array())
    {
        $local = $this->nav->get_css_includes();
        return parent::get_css_includes($local);
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = $this->nav->get_js_includes();
        return parent::get_js_includes($local);
    }
}
?>
