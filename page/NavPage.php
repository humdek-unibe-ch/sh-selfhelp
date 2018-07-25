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
    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and adds a nav component and a footer component to the page.
     *
     * @param object $router
     *  The router instance is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param string $keyword
     *  The identification name of the page.
     */
    public function __construct($router, $db, $keyword)
    {
        parent::__construct($router, $db, $keyword);
        $this->add_component("nav",
            new NavComponent($this->router, $this->db, $this->acl));
        $this->add_component("footer",
            new FooterComponent($this->router, $this->db, $this->acl));
    }

    /* Protected Methods ******************************************************/

    /**
     * Render the content of the page and add a navigation bar and a footer.
     */
    protected function output_base_content()
    {
        $this->output_component("nav");
        parent::output_base_content();
        $this->output_component("footer");
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::get_css_includes()
     */
    protected function get_css_includes() { return array(); }

    /**
     * See BasePage::get_js_includes()
     */
    protected function get_js_includes() { return array(); }

    /* Protected Abstract Methods *********************************************/

    /**
     * Render the content of the page.
     */
    abstract protected function output_content();

    /**
     * See BasePage::output_meta_tags()
     */
    abstract protected function output_meta_tags();
}
?>
