<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/NavSectionView.php";
require_once __DIR__ . "/NavSectionModel.php";

/**
 * A component to allow to navigate sections.
 *
 * The navSection component builds a hierarchical naviagion element from
 * sections. The section dependencies are defined in the 'sections_navigation'
 * database table. This table is specifically used for this component.
 *
 * Note that the 'sections_hierarchy' database table has the same structure but
 * serves to hierarchically compose sections without building a navigation
 * dependency.
 */
class NavSectionComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    private $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the SessionsNavModel class and the
     * SessionsNavView class and passes the view instance to the constructor of
     * the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param string $root_id
     *  The identifier of the navigation section.
     * @param int $current_id
     *  The id of the current section.
     */
    public function __construct($services, $root_id, $current_id=0)
    {
        $this->model = new NavSectionModel($services, $root_id, $current_id);
        $view = new NavSectionView($this->model);
        parent::__construct($view);
    }

    /**
     * Gets the number of root naviagtion items.
     *
     * @retval int
     *  The number of root navigation items.
     */
    public function get_count()
    {
        return $this->model->get_count();
    }

    /**
     * Gets the next section id given the current id.
     *
     * @retval int
     *  The next section id or false if it does not exist.
     */
    public function get_next_id()
    {
        return $this->model->get_next_id();
    }

    /**
     * Gets the previous section id given the current id.
     *
     * @retval int
     *  The previous section id or false if it does not exist.
     */
    public function get_previous_id()
    {
        return $this->model->get_previous_id();
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
        $local = array(__DIR__ . "/navSection.css");
        return parent::get_css_includes($local);
    }
}
?>
