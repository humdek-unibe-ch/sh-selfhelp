<?php
require_once __DIR__ . "/InternalPage.php";
require_once __DIR__ . "/../service/PageDb.php";
require_once __DIR__ . "/../service/globals_untracked.php";
require_once __DIR__ . "/../service/Login.php";
require_once __DIR__ . "/../service/Acl.php";
require_once __DIR__ . "/../service/Navigation.php";
require_once __DIR__ . "/../service/ParsedownExtension.php";
require_once __DIR__ . "/../service/ext/Gump.php";
require_once __DIR__ . "/../service/UserInput.php";
require_once __DIR__ . "/../component/style/StyleComponent.php";
require_once __DIR__ . "/../component/nav/NavComponent.php";
require_once __DIR__ . "/../component/footer/FooterComponent.php";

/**
 * This abstract class serves as staring point for pages.
 * It allow to render the basic html header elememnts, css, and js files.
 */
abstract class BasePage
{
    /* Private Properties *****************************************************/

    /**
     * An array of css include paths.
     */
    private $css_includes;

    /**
     * An array of js include paths.
     */
    private $js_includes;

    /**
     * An array of components assigned to this page.
     */
    private $components;

    /**
     * A flag indicating whether the navigation bar will be rendered.
     */
    private $render_nav;

    /**
     * A flag indicating whether the footer will be rendered.
     */
    private $render_footer;

    /* Protected Properties ***************************************************/

    /**
     * The title of the page.
     */
    protected $title;

    /**
     * The keyword of the page with which the page is identified by the router.
     */
    protected $keyword;

    /**
     * The unique id of the page.
     */
    protected $id_page;

    /**
     * The id of the navigation section assigned to this page.
     */
    protected $id_navigation_section;

    /**
     * The url of the page.
     */
    protected $url;

    /**
     * The required access level to acces the page.
     */
    protected $required_access_level;

    /**
     * An associative array holding the different available services. See the
     * class definition basepage for a list of all services.
     */
    protected $services;

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
            "/css/ext/bootstrap.min.css",
            "/css/ext/fontawesome.min.css",
        );
        $this->js_includes = array(
            "/js/ext/jquery.min.js",
            "/js/ext/popper.min.js",
            "/js/ext/bootstrap.min.js",
        );
        if(DEBUG == 0)
        {
            $this->css_includes[] = "/css/ext/styles.min.css";
            $this->js_includes[] = "/js/ext/styles.min.js";
        }
        $this->add_main_include_files(CSS_SERVER_PATH, "/css/",
            $this->css_includes);
        $this->add_main_include_files(JS_SERVER_PATH, "/js/",
            $this->js_includes);
        if(DEBUG == 1)
            $this->collect_style_includes();
        $this->services = array(
            // The router instance which is used to generate valid links.
            "router" => $router,
            //  The db instance which grants access to the DB.
            "db" => $db,
            // The login instance that allows to check user credentials.
            "login" => new Login($db, $router),
            // The instance of the access control layer (ACL) which allows to
            // decide which links to display.
            "acl" => new Acl($db),
            // A markdown parser.
            "parsedown" => new ParsedownExtension(),
            // The instance to the navigation service which allows to switch
            // between sections, associated to a specific page. Unlike the
            // other elements in this array, "nav" may be null if a page has
            // only one view.
            "nav" => null,
            // User input handler
            "user_input" => new UserInput($db),
        );
        $this->services['parsedown']->setSafeMode(false);
        $this->fetch_page_info($keyword);
        if($this->id_navigation_section != null)
            $this->services['nav'] = new Navigation($router, $db, $keyword,
                $this->id_navigation_section);
        $this->add_component("nav",
            new NavComponent($this->services));
        $this->add_component("footer",
            new FooterComponent($this->services));
    }

    /* Private Metods *********************************************************/

    /**
     * Iterate through all styles and collect all js and css files.
     */
    private function collect_style_includes()
    {
        if($handle = opendir(STYLE_SERVER_PATH)) {
            while(false !== ($file = readdir($handle)))
            {
                if(filetype(STYLE_SERVER_PATH . '/' . $file) !== "dir"
                    || $file === "." || $file === "..") continue;
                $this->add_main_include_files(
                    STYLE_SERVER_PATH . '/' . $file . '/css',
                    STYLE_PATH . '/' . $file . '/css/',
                    $this->css_includes
                );
                $this->add_main_include_files(
                    STYLE_SERVER_PATH . '/' . $file . '/js',
                    STYLE_PATH . '/' . $file . '/js/',
                    $this->js_includes
                );
            }
            closedir($handle);
        }
    }

    /**
     * Add include files to the list of includes.
     *
     * @param string $path
     *  The server path to the folder holding include files.
     * @param string $path_prefix
     *  The relative host path to reach the include files.
     * @param reference &$includes
     *  A reference to the array where the include paths will be attached.
     */
    private function add_main_include_files($path, $path_prefix, &$includes)
    {
        if(!file_exists($path)) return;
        $files = array();
        if($handle = opendir($path)) {
            while(false !== ($file = readdir($handle)))
            {
                if(filetype($path . '/' . $file) === "dir") continue;
                $files[] = $file;
            }
            closedir($handle);
        }
        natcasesort($files);
        foreach($files as $file)
            $includes[] = $path_prefix . $file;
    }

    /**
     * Return a valid js string definig global constants.
     *
     * @retval string
     *  A string of valid js code.
     */
    private function get_js_constants()
    {
        return 'const BASE_PATH = "' . BASE_PATH . '";';
    }

    /**
     * Return a valid string of csp rules.
     *
     * @retval string
     *  A string of valid csp rules.
     */
    private function get_csp_rules()
    {
        return "default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'sha256-"
            . base64_encode(hash('sha256', $this->get_js_constants(), true)) . "'; img-src 'self' data: https://via.placeholder.com/";
    }

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
        $this->required_access_level = $info['access_level'];
        if($info['is_headless']) $this->disable_navigation();
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

    /* Protected Methods ******************************************************/

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
        if($this->services['acl']->has_access($_SESSION['id_user'],
                $this->id_page, $this->required_access_level))
            $this->output_content();
        else if($this->services['login']->is_logged_in())
        {
            $page = new InternalPage($this, "no_access");
            $page->output_content();
        }
        else
        {
            $page = new InternalPage($this, "no_access_guest");
            $page->output_content();
        }
        if($this->render_footer) $this->output_component("footer");
    }

    /**
     * Render the meta tags of the page.
     */
    protected function output_meta_tags()
    {
        require __DIR__ . "/tpl_meta.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Adds a component to the list of components of this page.
     * The js and css include list is extended by the component includes.
     *
     * @param string $key
     *  A unique component identifier.
     * @param object $component
     *  The component instance to be added.
     */
    public function add_component($key, $component)
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
     * Do not render the navigation bar and the footer.
     */
    public function disable_navigation()
    {
        $this->render_nav = false;
        $this->render_footer = false;
    }

    /**
     * Gets a component, given a key.
     *
     * @param string $key
     *  The unique identifier of the component.
     * @retval object
     *  The component if it exists or null otherwise
     */
    public function get_component($key)
    {
        if(array_key_exists($key, $this->components))
            return $this->components[$key];
        return null;
    }

    /**
     * Return the array of initialised services.
     *
     * @retval array
     *  The array of services.
     */
    public function get_services()
    {
        return $this->services;
    }

    /**
     * Render the page view.
     */
    public function output()
    {
        $title = $this->title;
        require_once __DIR__ . '/tpl_page.php';
    }

    /**
     * Renders the content of the component.
     *
     * @param string $key
     *  The unique identifier of the component.
     */
    public function output_component($key)
    {
        $component = $this->get_component($key);
        if($component != null)
            $component->output_content();
    }
}
?>
