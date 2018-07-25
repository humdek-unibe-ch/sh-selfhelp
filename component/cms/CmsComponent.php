<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/CmsView.php";
require_once __DIR__ . "/CmsModel.php";
require_once __DIR__ . "/CmsController.php";

/**
 * The cms component.
 */
class CmsComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CmsModel class, the CmsView
     * class, and the CmsController class and passes the view instance to the
     * constructor of the parent class.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($router, $db)
    {
        $model = new CmsModel($router, $db);
        $controller = new CmsController();
        $view = new CmsView($model, $controller);
        parent::__construct($view);
    }
}
?>
