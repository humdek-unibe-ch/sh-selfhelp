<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/StyleView.php";
require_once __DIR__ . "/StyleModel.php";

/**
 * The class to define the style component. A style component serves to render
 * section content that is stored in the database with variable views.
 * The views are specified by the style.
 */
class StyleComponent extends BaseComponent
{
    private $children;
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the StyleModel class and the
     * StyleView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param bool $fluid
     *  If set to true the content will be rendered in a container-fluid
     *  bootstrap element, if set to false in a container. The defualt is true.
     */
    public function __construct($router, $db, $id, $fluid=true)
    {
        $this->children = array();
        $db_children = $db->fetch_section_children($id);
        foreach($db_children as $child)
            array_push($this->children,
                new StyleComponent($router, $db, $child['id']));
        $model = new StyleModel($router, $db, $id, $this->children);
        $view = new StyleView($model, $fluid);
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
        $res = parent::get_css_includes();
        foreach($this->children as $child)
            $res = array_merge($res, $child->get_css_includes());
        return array_unique($res);
    }
}
?>
