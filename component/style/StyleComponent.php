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
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param bool $fluid
     *  If set to true the content will be rendered in a container-fluid
     *  bootstrap element, if set to false in a container. The defualt is true.
     */
    public function __construct($services, $id, $fluid=true)
    {
        $model = new StyleModel($services, $id);
        $view = new StyleView($model, $fluid, $services['nav']);
        parent::__construct($view);
    }
}
?>
