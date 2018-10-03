<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/BaseStyleComponent.php";
require_once __DIR__ . "/StyleView.php";
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
    private $style;

    /**
     * A flag indicating whther the style is known or whether the style name is
     * invalid.
     */
    private $is_style_known;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the StyleModel class and the
     * StyleView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param array $params
     *  An array of parameter that will be passed to the style component.
     */
    public function __construct($services, $id, $params=array())
    {
        $model = new StyleModel($services, $id, $params);
        $this->is_style_known = true;
        if($model->get_style_type() == "view")
        {
            $this->style = new BaseStyleComponent($model->get_style_name(),
                array( "children" => $model->get_children()),
                $model->get_db_fields());
        }
        else if($model->get_style_type() == "component"
            || $model->get_style_type() == "navigation")
        {
            $className = ucfirst($model->get_style_name()) . "Component";
            if(class_exists($className))
                $this->style = new $className($services, $id, $params);
            else
                $this->style = new BaseStyleComponent("unknownStyle",
                    array("style_name" => $model->get_style_name()));
        }
        else
        {
            $this->is_style_known = false;
            return;
        }
        $view = new StyleView($model, $this->style, true);
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
}
?>
