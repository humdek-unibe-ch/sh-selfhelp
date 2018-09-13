<?php
require_once __DIR__ . "/InternalPage.php";
require_once __DIR__ . "/BasePage.php";

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
    private $componentInstance;

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
    public function __construct($router, $db, $keyword, $params)
    {
        parent::__construct($router, $db, $keyword);
        $componentClass = ucfirst($keyword) . "Component";
        $this->componentInstance = new $componentClass($this->services,
            $params);
        $this->add_component("comp", $this->componentInstance);
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        if($this->componentInstance->has_access())
            $this->output_component("comp");
        else
        {
            $page = new InternalPage($this, "missing");
            $page->output_content();
        }
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
