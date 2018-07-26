<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/NavView.php";
require_once __DIR__ . "/NavModel.php";

/**
 * The class to define the navigation component. This component has a
 * non-standard model which constructs the hierarchical menu structure from the
 * pages database table.
 */
class NavComponent extends BaseComponent
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
     * @param object $acl
     *  The instnce of the access control layer (ACL) which allows to decide
     *  which links to display.
     */
    public function __construct($services)
    {
        $model = new NavModel($services['router'], $services['db'],
            $services['acl']);
        $view = new NavView($model);
        parent::__construct($view);
    }
}
?>
