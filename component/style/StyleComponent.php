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
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the NavModel class and the
     * NavView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param string $style
     *  A string specifying the syle to be used to render the content.
     * @param bool $fluid
     *  If set to true the content will be rendered in a container-fluid
     *  bootstrap element, if set to false in a container. The defualt is true.
     */
    public function __construct($router, $db, $id, $style, $fluid=true)
    {
        $model = new StyleModel($router, $db, $id);
        $view = new StyleView($model, $style, $fluid);
        parent::__construct($view);
    }
}
?>
