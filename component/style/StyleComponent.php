<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/BaseStyleComponent.php";
require_once __DIR__ . "/StyleView.php";
require_once __DIR__ . "/StyleModel.php";

/**
 * The class to define the style component. A style component serves to render
 * section content that is stored in the database with variable views.
 * The views are specified by the style.
 */
class StyleComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

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
     * @param int $id_active
     *  The id of the currently active section (this is used for the cms)
     */
    public function __construct($services, $id, $id_active=null)
    {
        $model = new StyleModel($services, $id, $id_active);
        $this->is_style_known = true;
        if($model->get_style_type() == "view")
        {
            $style = new BaseStyleComponent($model->get_style_name(),
                array( "children" => $model->get_children()), true,
                $model->get_db_fields());
        }
        else if($model->get_style_type() == "component")
        {
            $className = ucfirst($model->get_style_name()) . "Component";
            $style = new $className($services, $id, $id_active);
        }
        else if($model->get_style_type() == "navigation")
        {
            throw new Exception("cannot render a navigation style");
        }
        else
        {
            $this->is_style_known = false;
            return;
        }
        $view = new StyleView($model, $style, true);
        parent::__construct($view);
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
        return parent::has_access() && $this->is_style_known;
    }
}
?>
