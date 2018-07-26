<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/FooterView.php";
require_once __DIR__ . "/FooterModel.php";

/**
 * The footer component. The footer component is similar to the nav component
 * but is simpler as it does not support a hierarchical menu structure.
 */
class FooterComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the FooterModel class and the
     * FooterView class and passes the view instance to the constructor of the
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
        $model = new FooterModel($services['router'], $services['db'],
            $services['acl']);
        $view = new FooterView($model);
        parent::__construct($view);
    }
}
?>
