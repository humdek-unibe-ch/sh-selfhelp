<?php
require_once __DIR__ . "/../service/PageDb.php";
require_once __DIR__ . "/../service/globals_untracked.php";
require_once __DIR__ . "/../service/Login.php";
require_once __DIR__ . "/../service/Acl.php";
require_once __DIR__ . "/../service/Navigation.php";
require_once __DIR__ . "/../service/Parsedown.php";
require_once __DIR__ . "/../component/style/StyleComponent.php";
require_once __DIR__ . "/../component/nav/NavComponent.php";
require_once __DIR__ . "/../component/footer/FooterComponent.php";
require_once __DIR__ . "/../component/cms/CmsComponent.php";
require_once __DIR__ . "/../component/user/UserComponent.php";
require_once __DIR__ . "/../component/group/GroupComponent.php";

/**
 * This abstract class serves as staring point for pages.
 * It allow to render the basic html header elememnts, css, and js files.
 */
abstract class BasePage
{
    /* Private Properties *****************************************************/

    protected $title;
    protected $keyword;
    protected $id_page;
    protected $id_navigation_section;
    protected $url;
    protected $services;
    private $css_includes;
    private $js_includes;
    private $components;
    private $render_nav;
    private $render_footer;

    /* Constructors ***********************************************************/

    /**
     * The constructor initialises the css and js include arrays with the
     * base files that are common for all pages.
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
        $this->render_nav = true;
        $this->render_footer = true;
        $this->keyword = $keyword;
        $this->components = array();
        $this->css_includes = array(
            "/css/bootstrap.min.css",
            "/css/fontawesome.css",
            "/css/main.css",
        );
        $this->js_includes = array(
            "/js/jquery.min.js",
            "/js/bootstrap.min.js",
            "/js/main.js",
        );
        $this->services = array(
            // The router instance which is used to generate valid links.
            "router" => $router,
            //  The db instance which grants access to the DB.
            "db" => $db,
            // The login instance that allows to check user credentials.
            "login" => new Login($db),
            // The instnce of the access control layer (ACL) which allows to
            // decide which links to display.
            "acl" => new Acl($db),
            // A markdown parser.
            "parsedown" => new Parsedown(),
            // The instance to the navigation service which allows to switch
            // between sections, associated to a specific page. Unlike the
            // other elements in this array, "nav" may be null if a page has
            // only one view.
            "nav" => null,
        );
        $this->services['parsedown']->setSafeMode(true);
        $this->fetch_page_info($keyword);
        if($this->id_navigation_section != null)
            $this->services['nav'] = new Navigation($router, $db, $keyword,
                $this->id_navigation_section);
        $this->add_component("denied-guest",
            new StyleComponent($this->services, NO_ACCESS_GUEST_ID));
        $this->add_component("denied",
            new StyleComponent($this->services, NO_ACCESS_ID));
        $this->add_component("missing",
            new StyleComponent($this->services, MISSING_ID));
        $this->add_component("nav",
            new NavComponent($this->services));
        $this->add_component("footer",
            new FooterComponent($this->services));
    }

    /* Private Metods *********************************************************/

    /**
     * Fetch the main page information from the database.
     *
     * @param string $keyword
     *  The keyword identifying the page.
     */
    private function fetch_page_info($keyword)
    {
        $info = $this->services['db']->fetch_page_info($keyword);
        $this->title = $info['title'];
        $this->url = $info['url'];
        $this->id_page = intval($info['id']);
        $this->id_navigation_section = null;
        if($info['id_navigation_section'] != null)
            $this->id_navigation_section = intval($info['id_navigation_section']);
    }

    /**
     * Add page include files and render the css include directives.
     */
    private function output_css_includes()
    {
        $this->css_includes = array_unique(array_merge($this->css_includes,
            $this->get_css_includes()));
        foreach($this->css_includes as $css_include)
        {
            $include_path = $this->services['router']->get_asset_path($css_include);
            require __DIR__ . '/tpl_css_include.php';
        }
    }

    /**
     * Add page include files and render the js include directives.
     */
    private function output_js_includes()
    {
        $this->js_includes = array_unique(array_merge($this->js_includes,
            $this->get_js_includes()));
        foreach($this->js_includes as $js_include)
        {
            $include_path = $this->services['router']->get_asset_path($js_include);
            require __DIR__ . '/tpl_js_include.php';
        }
    }

    /* Protected Abstract Methods ***********************************************/

    /**
     * Render the content of the page.
     * This function needs to be implemented by the class extending the BasePage.
     */
    abstract protected function output_content();

    /**
     * Render the meta tags of the page.
     * This function needs to be implemented by the class extending the BasePage.
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
     * Gets a component, given a key.
     *
     * @param string $key
     *  The unique identifier of the component.
     * @retval object
     *  The component if it exists or null otherwise
     */
    protected function get_component($key)
    {
        if(array_key_exists($key, $this->components))
            return $this->components[$key];
        return null;
    }

    /**
     * Get custom css include files required to style the page.
     *
     * @retval array
     *  An array of css include file paths.
     */
    protected function get_css_includes() { return array(); }

    /**
     * Get custom js include files to perform page specific client-side
     * computations.
     *
     * @retval array
     *  An array of js include file paths.
     */
    protected function get_js_includes() { return array(); }

    /**
     * Render the content of the page.
     */
    protected function output_base_content()
    {
        if($this->render_nav) $this->output_component("nav");
        if($this->services['acl']->has_access_select($_SESSION['id_user'],
            $this->id_page))
            $this->output_content();
        else if($this->services['login']->is_logged_in())
            $this->output_component("denied");
        else
            $this->output_component("denied-guest");
        if($this->render_footer) $this->output_component("footer");
    }

    /**
     * Renders the content of the component.
     *
     * @param string $key
     *  The unique identifier of the component.
     */
    protected function output_component($key)
    {
        $component = $this->get_component($key);
        if($component != null)
            $component->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Do not render the navigation bar and the footer.
     */
    public function disable_navigation()
    {
        $this->render_nav = false;
        $this->render_footer = false;
    }

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
