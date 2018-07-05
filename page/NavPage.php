<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/../component/nav/NavComponent.php";
require_once __DIR__ . "/../component/footer/FooterComponent.php";

/**
 * This abstract class is a simple wrapper for the Base Page which allows to
 * render a page with navigation bar and footer.
 */
abstract class NavPage extends BasePage
{
    private $css_includes;
    private $js_includes;

    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and adds a nav component and a footer component to the page.
     *
     * @param object $router
     *  The router instance is used to generate valid links.
     */
    public function __construct($router, $title)
    {
        $this->css_includes = array();
        $this->js_includes = array();
        parent::__construct($router, $title);
        $this->add_component("nav", new NavComponent($router));
        $this->add_component("footer", new FooterComponent($router));
    }

    /* Protected Methods ******************************************************/

    /**
     * Render the content of the page and add a navigation bar and a footer.
     */
    protected function output_base_content()
    {
        $this->output_component("nav");
        $this->output_content();
        $this->output_component("footer");
    }

    /* Protected Abstract Methods *********************************************/

    /**
     * Render the content of the page.
     */
    abstract protected function output_content();

    /**
     * See BasePage::output_meta_tags()
     */
    abstract protected function output_meta_tags();

    /**
     * See BasePage::get_css_includes()
     */
    protected function get_css_includes() { return $this->css_includes; }

    /**
     * See BasePage::get_js_includes()
     */
    protected function get_js_includes() { return $this->js_includes; }
}
?>
