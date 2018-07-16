<?php
/**
 * This abstract class serves as staring point for pages.
 * It allow to render the basic html header elememnts, css, and js files.
 */
abstract class BasePage
{
    /* Private Properties *****************************************************/

    protected $router;
    private $title;
    private $css_includes;
    private $js_includes;
    private $components;

    /* Constructors ***********************************************************/

    /**
     * The constructor initialises the css and js include arrays with the
     * base files that are common for all pages.
     *
     * @param object $router
     *  The router instance is used to generate valid links.
     */
    public function __construct($router, $title)
    {
        $this->router = $router;
        $this->title = $title;
        $this->components = array();
        $this->css_includes = array(
            "/css/bootstrap.min.css",
            "/css/main.css"
        );
        $this->js_includes = array(
            "/js/jquery.min.js",
            "/js/bootstrap.min.js",
            "/js/main.js"
        );
    }

    /* Private Metods *********************************************************/

    /**
     * Add page include files and render the css include directives.
     */
    private function output_css_includes()
    {
        $this->css_includes = array_merge($this->css_includes,
            $this->get_css_includes());
        foreach($this->css_includes as $css_include)
        {
            $include_path = $this->router->get_asset_path($css_include);
            require __DIR__ . '/tpl_css_include.php';
        }
    }

    /**
     * Add page include files and render the js include directives.
     */
    private function output_js_includes()
    {
        $this->js_includes = array_merge($this->js_includes,
            $this->get_js_includes());
        foreach($this->js_includes as $js_include)
        {
            $include_path = $this->router->get_asset_path($js_include);
            require __DIR__ . '/tpl_js_include.php';
        }
    }

    /* Protected Abstract Methods ***********************************************/

    /**
     * Get custom css include files required to style the page.
     *
     * @retval array
     *  An array of css include file paths.
     */
    abstract protected function get_css_includes();

    /**
     * Get custom js include files to perform page specific client-side
     * computations.
     *
     * @retval array
     *  An array of js include file paths.
     */
    abstract protected function get_js_includes();

    /**
     * Render the content of the page.
     */
    abstract protected function output_base_content();

    /**
     * Render the meta tags of the page.
     */
    abstract protected function output_meta_tags();

    /* Protected Methods ******************************************************/

    /**
     * Adds a component to the list of components of this page.
     * The js and css include list is extended by the component includes.
     *
     * @param string $key
     *  A unique component identifier.
     * @param object $component
     *  The component instance to be added.
     */
    protected function add_component($key, $component)
    {
        if(array_key_exists($key, $this->components))
            throw new Exception("Component $key already exists.");
        $this->components[$key] = $component;
        $this->css_includes = array_merge($this->css_includes,
            $component->get_css_includes());
        $this->js_includes = array_merge($this->js_includes,
            $component->get_js_includes());
    }

    /**
     * Renders the content of the component.
     *
     * @param string $key
     *  The unique identifier of the component.
     */
    protected function output_component($key)
    {
        $this->components[$key]->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the page view.
     */
    public function output()
    {
        $title = $this->title;
        require_once __DIR__ . '/tpl_page.php';
    }
}
?>
