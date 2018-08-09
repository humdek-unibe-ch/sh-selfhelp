<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/MarkdownView.php";
require_once __DIR__ . "/MarkdownModel.php";

/**
 * A component to for a markdown style element
 */
class MarkdownComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the MarkdownModel class and the
     * MarkdownView class and passes the view instance to the constructor of the
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
        $model = new MarkdownModel($services, $id, $id_active);
        $view = new MarkdownView($model);
        parent::__construct($view);
    }
}
?>
