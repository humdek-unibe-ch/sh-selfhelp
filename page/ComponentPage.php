<?php
require_once __DIR__ . "/BasePage.php";
spl_autoload_register(function ($class_name) {
    $folder = strtolower(str_replace("Component", "", $class_name));
    require_once __DIR__ . "/../component/" . $folder . "/" . $class_name . ".php";
});

/**
 * This class creates a page from a single component where the component class
 * matches with the page keyword according to the following naming conventions:
 * A page with the keyword "foo" will search for the component class
 * "component/foo/FooComponent.php".
 */
class ComponentPage extends BasePage
{
    /* Private Properties *****************************************************/

    private $sections;

    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and instanciates the specified component.
     *
     * @param object $router
     *  The router instance is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param string $component
     *  The component name. This name will be used to construct the class name.
     */
    public function __construct($router, $db, $keyword, $id=null)
    {
        parent::__construct($router, $db, $keyword, $id);
        $componentClass = ucfirst($keyword) . "Component";
        $this->add_component("comp", new $componentClass($this->services, $id));
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        $this->output_component("comp");
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
