<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/FooterView.php";
require_once __DIR__ . "/FooterModel.php";

/**
 * The footer component.
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
    public function __construct($router, $db, $acl)
    {
        $model = new FooterModel($db, $acl);
        $view = new FooterView($router, $model);
        parent::__construct($view);
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the 
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes()
    {
        return array(
            __DIR__ . "/footer.css"
        );
    }
}
?>
