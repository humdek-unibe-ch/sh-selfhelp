<?
require_once __DIR__ . "/BasePage.php";

/**
 * This abstract class is a simple wrapper for the Base Page which allows to
 * render a plain page.
 */
abstract class Page extends BasePage
{
    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It only calls the constructor of the
     * parent class.
     *
     * @param object $router
     *  The router instance is used to generate valid links.
     */
    public function __construct($router, $title)
    {
        parent::__construct($router, $title);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the content of the page.
     */
    private function output_base_content()
    {
        $this->output_content;
    }

    /* Private Abstract Methods ***********************************************/

    /**
     * See BasePage::output_content()
     */
    abstract private function output_content();

    /**
     * See BasePage::output_meta_tags()
     */
    abstract private function output_meta_tags();

    /**
     * See BasePage::get_css_includes()
     */
    abstract private function get_css_includes();

    /**
     * See BasePage::get_js_includes()
     */
    abstract private function get_js_includes();
}
?>
