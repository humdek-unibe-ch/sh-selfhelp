<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/NavSectionView.php";
require_once __DIR__ . "/NavSectionModel.php";

/**
 * A component to allow to navigate sections.
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
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param string $keyword
     *  The identifier of the navication section.
     * @param int $current_id
     *  The id of the current section.
     */
    public function __construct($router, $db, $keyword, $current_id=0)
    {
        $sql = "SELECT id FROM sections WHERE name=:keyword";
        $id_db = $db->query_db_first($sql, array(":keyword" => $keyword));
        if($id_db) $root_id = $id_db['id'];
        else throw new Exception("unknown navigation name '$keyword'");
        $this->model = new NavSectionModel($router, $db, $root_id, $current_id);
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
    public function get_css_includes()
    {
        return array(
            __DIR__ . "/navSection.css"
        );
    }
}
?>
