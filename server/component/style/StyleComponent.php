<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/BaseStyleComponent.php";
require_once __DIR__ . "/StyleModel.php";

/**
 * The class to define the style component. A style component serves to render
 * section content that is stored in the database with variable views.
 * The views are specified by the style.
 *
 * Styles are registered in the database. A style is loaded by name matching.
 * The name of the style must be matchable to the path and the name of the
 * class that will be instantiated.  A style can either be a simple view or
 * a fully fledget component. Depending on this tha class to be instantiated is
 * postfixe by 'View' or 'Component', respectively.  E.g. when using the view
 * style 'myVStyle' the following class will be loaded and instantiated:
 * 'server/style/myVStyle/MyVStyleView.php' (Note the capital first letter of
 * the class name).
 */
class StyleComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    /**
     * The component instance of the style.
     */
    private $style = null;

    /**
     * A flag indicating whther the style is known or whether the style name is
     * invalid.
     */
    private $is_style_known;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the StyleModel class and passes
     * the view instance of the style to render to the constructor of the
     * parent class.
     *
     * @param object $services
     *  The service handler instance which holds all services
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param array $params
     *  An array of parameter that will be passed to the style component.
     */
    public function __construct($services, $id, $params=array())
    {
        $model = null;
        $this->is_style_known = true;
        // get style name and type
        $db = $services->get_db();
        $sql = "SELECT s.name, t.name AS type
            FROM styles AS s
            LEFT JOIN styleType AS t ON t.id = s.id_type
            LEFT JOIN sections AS sec ON sec.id_styles = s.id
            WHERE sec.id = :id";
        $style = $db->query_db_first($sql, array(":id" => $id));
        if(!$style) {
            $this->is_style_known = false;
            return;
        }

        if($style['type'] == "view")
        {
            $model = new StyleModel($services, $id, $params);
            $this->style = new BaseStyleComponent($model->get_style_name(),
                array( "children" => $model->get_children()),
                $model->get_db_fields());
        }
        else if($style['type'] == "component" || $style['type'] == "navigation")
        {
            $className = ucfirst($style['name']) . "Component";
            if(class_exists($className))
                $this->style = new $className($services, $id, $params);
            if($this->style === null || !$this->style->has_access())
                $this->style = new BaseStyleComponent("unknownStyle",
                    array("style_name" => $style['name']));
        }
        else
        {
            $this->is_style_known = false;
            return;
        }
        $view = $this->style->get_view();
        parent::__construct($model, $view);
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function to deny access on invalid styles.
     *
     * @retval bool
     *  True if the style is known, false otherwise
     */
    public function has_access()
    {
        return parent::has_access() && $this->is_style_known
            && $this->style->has_access();
    }

    /**
     * Returns the reference to the instance of a style class.
     *
     * @retval reference
     *  Refernce to the style instance class.
     */
    public function &get_style_instance()
    {
        return $this->style;
    }

    /**
     * Search for a child section of a specific name.
     *
     * @param string $name
     *  The name of the section to be seacrhed
     * @retval reference
     *  Reference to the section instance.
     */
    public function &get_child_section_by_name($name)
    {
        return $this->model->get_child_section_by_name($name);
    }
}
?>
