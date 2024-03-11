<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/InternalPage.php";
require_once __DIR__ . "/../component/style/StyleComponent.php";
require_once __DIR__ . "/../component/nav/NavComponent.php";
require_once __DIR__ . "/../component/footer/FooterComponent.php";
require_once __DIR__ . "/../component/style/StyleView.php";

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
     * An array of js include paths which shouldbe loaded after the styles' JS
     */
    private $js_includes_after;

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

    /**
     * A flag to indicate whether the acl check was successful or not.
     */
    protected $acl_pass = false;

    /**
     * Page Access type, it can be mobile, web and mobile_and_web
     */
    protected $pageAccessType;

    /**
     * Page object containing all the info for the page
     */
    protected $page;

    /* Constructors ***********************************************************/

    /**
     * The constructor initialises the css and js include arrays with the
     * base files that are common for all pages.
     *
     * @param object $services
     *  The service handler instance which holds all services
     * @param string $keyword
     *  The identification name of the page.
     */
    public function __construct($services, $keyword)
    {
        $this->services = $services;
        $this->render_nav = true;
        $this->render_footer = true;
        $this->keyword = $keyword;
        $this->components = array();
        $this->css_includes = array(            
            "/css/ext/bootstrap.min.css",          
            // "/css/ext/bootstrap3.3.1.min.css",  
            "/css/ext/fontawesome.min.css",
            "/css/ext/datatables.min.css",
            "/css/ext/bootstrap-select.min.css",
            "/css/ext/jquery-confirm.min.css",
            "/css/ext/flatpickr.min.css",
            "/css/ext/iconselect.css",
            "/css/ext/editor.main.css",
            "/css/ext/query-builder.default.min.css",
            "/css/ext/query-builder.bootstrap.css",            
            "/css/ext/bootstrap4-toggle.min.css",      
            "/css/ext/easymde.min.css",   
            "/css/ext/dataTables.contextualActions.min.css",             
            "/css/ext/jstree3.3.16.min.css"   
        );
        $this->js_includes = array(
            "/js/ext/jquery.min.js",
            "/js/ext/runtime.js",
            "/js/ext/bootstrap.bundle.min.js",
            "/js/ext/datatables.min.js",
            "/js/ext/bootstrap-select.min.js",
            "/js/ext/jquery-confirm.min.js",
            "/js/ext/flatpickr.min.js",
            "/js/ext/html2pdf.bundle.min.js",
            "/js/ext/iconselect.js",
            "/js/ext/iscroll.js",
            "/js/ext/jquery-ui.min.js",
            "/js/ext/jsoneditor.min.js",
            "/js/ext/moment.min.js",
            "/js/ext/jquery-extendext.js",
            "/js/ext/query-builder.standalone.min.js",
            "/js/ext/bootstrap4-toggle.min.js",
            "/js/ext/easymde.min.js",
            "/js/ext/dataTables.contextualActions.min.js",
            "/js/ext/css-format-monaco.min.js", 
            "/js/ext/jstree3.3.16.min.js" 
        );
        $this->js_includes_after = array(
            "/js/ext/loader.js",
        );
        if(DEBUG == 0)
        {
            $this->css_includes[] = "/css/ext/styles.min.css?v=" . $this->services->get_db()->get_git_version();
            $this->js_includes[] = "/js/ext/styles.min.js?v=" . $this->services->get_db()->get_git_version();
        }
        $this->add_main_include_files(CSS_SERVER_PATH, "/css/", "css",
            $this->css_includes);
        $this->add_main_include_files(JS_SERVER_PATH, "/js/", "js",
            $this->js_includes);
        if(DEBUG == 1) {
            $this->collect_style_includes();            
        }
        if($this->is_cms_page()){
            $this->collect_plugin_includes();
        }        
        $this->fetch_page_info($keyword);
        if($this->id_navigation_section != null)
            $this->services->set_nav(new Navigation(
                $this->services->get_router(), $this->services->get_db(),
                $keyword, $this->id_navigation_section));
        $this->add_component("nav",
            new NavComponent($this->services));
        $this->add_component("footer",
            new FooterComponent($this->services));
        $acl = $this->services->get_acl();
        $this->acl_pass = $acl->has_access($_SESSION['id_user'],
                $this->id_page, $this->required_access_level);
    }

    /* Private Methods *********************************************************/    

    /**
     * Iterate through all styles and collect all js and css files.
     */
    private function collect_style_includes()
    {
        $js_includes = array();
        $css_includes = array();
        if($handle = opendir(STYLE_SERVER_PATH)) {
            $this->add_main_include_files(
                STYLE_SERVER_PATH . '/css',
                STYLE_PATH . '/css/', 'css',
                $this->css_includes
            );
            $this->add_main_include_files(
                STYLE_SERVER_PATH . '/js',
                STYLE_PATH . '/js/', 'js',
                $this->js_includes
            );
            while(false !== ($file = readdir($handle)))
            {
                if(filetype(STYLE_SERVER_PATH . '/' . $file) !== "dir"
                        || $file === "." || $file === ".."
                        || $file === "js" || $file === "css" )
                    continue;
                $this->add_main_include_files(
                    STYLE_SERVER_PATH . '/' . $file . '/css',
                    STYLE_PATH . '/' . $file . '/css/', 'css',
                    $css_includes
                );
                $this->add_main_include_files(
                    STYLE_SERVER_PATH . '/' . $file . '/js',
                    STYLE_PATH . '/' . $file . '/js/', 'js',
                    $js_includes
                );
            }
            closedir($handle);
        }
        sort($js_includes);
        sort($css_includes);
        $this->js_includes = array_merge($this->js_includes, $js_includes);
        $this->css_includes = array_merge($this->css_includes, $css_includes);
    }

    /**
     * Iterate through all plugins and collect all js and css files.
     */
    private function collect_plugin_includes()
    {
        $js_includes = array();
        $css_includes = array();
        if(DEBUG){
            $css_location = '/css/';
            $js_location = '/js/';
        }else{
            $css_location = '/css/ext/';
            $js_location = '/js/ext/';
        }
        if($handle = opendir(PLUGIN_SERVER_PATH)) {
            $this->add_main_include_files(
                PLUGIN_SERVER_PATH . $css_location,
                PLUGIN_PATH . $css_location, 'css',
                $this->css_includes
            );
            $this->add_main_include_files(
                PLUGIN_SERVER_PATH . $js_location,
                PLUGIN_PATH . $js_location, 'js',
                $this->js_includes
            );
            while (false !== ($file = readdir($handle))) {
                if (filetype(PLUGIN_SERVER_PATH . '/' . $file) !== "dir" || $file === "." || $file === ".." || $file === "js" || $file === "css") {
                    continue;
                }
                if (DEBUG) {
                    $component_path = PLUGIN_SERVER_PATH . '/' . $file . '/server/component/style';
                    $plugin_path = PLUGIN_PATH . '/' . $file . '/server/component/style';
                    if (file_exists($component_path) && $handle2 = opendir($component_path)) {
                        while (false !== ($dir = readdir($handle2))) {
                            if (filetype($component_path . '/' . $dir) !== "dir" || $dir === "." || $dir === ".." || $dir === "js" || $dir === "css") {
                                continue;
                            }
                            $this->add_main_include_files(
                                $component_path . '/' . $dir    . $css_location,
                                $plugin_path  . '/' . $dir    . $css_location,
                                'css',
                                $css_includes
                            );
                            $this->add_main_include_files(
                                $component_path . '/' . $dir    . $js_location,
                                $plugin_path . '/' . $dir   . $js_location,
                                'js',
                                $js_includes
                            );
                        }
                    }
                } else {
                    $this->add_main_include_files(
                        PLUGIN_SERVER_PATH . '/' . $file    . $css_location,
                        PLUGIN_PATH . '/' . $file    . $css_location,
                        'css',
                        $css_includes
                    );
                    $this->add_main_include_files(
                        PLUGIN_SERVER_PATH . '/' . $file    . $js_location,
                        PLUGIN_PATH . '/' . $file   . $js_location,
                        'js',
                        $js_includes
                    );
                }
            }
            closedir($handle);
        }
        sort($js_includes);
        sort($css_includes);
        $this->js_includes = array_merge($this->js_includes, $js_includes);
        $this->css_includes = array_merge($this->css_includes, $css_includes);
    }

    /**
     * Add include files to the list of includes.
     *
     * @param string $path
     *  The server path to the folder holding include files.
     * @param string $path_prefix
     *  The relative host path to reach the include files.
     * @param string $extension
     *  The file extension of the file to be added.
     * @param reference &$includes
     *  A reference to the array where the include paths will be attached.
     */
    private function add_main_include_files($path, $path_prefix, $extension,
        &$includes)
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
        {
            $file_parts = pathinfo($file);
            if($file_parts['extension'] === $extension)
                $includes[] = $path_prefix . $file;
        }
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
    private function getCspRules()
    {
        $csp_rules = "default-src 'self'; font-src 'self' data:;  style-src 'self' 'unsafe-inline'; object-src 'none'; script-src 'self' 'unsafe-inline' 'sha256-"
            . base64_encode(hash('sha256', $this->get_js_constants(), true)) . "'; img-src 'self' blob: data: https://via.placeholder.com/;";
        return $csp_rules;
    }

    /**
     * Fetch the main page information from the database and add transaction to the logs
     *
     * @param string $keyword
     *  The keyword identifying the page.
     */
    private function fetch_page_info($keyword)
    {
        $db = $this->services->get_db();
        $info = $db->fetch_page_info($keyword);
        $transaction = $this->services->get_transaction();
        $transaction->add_transaction(            
            transactionTypes_select,
            transactionBy_by_user,
            $_SESSION['id_user'],
            $transaction::TABLE_PAGES,
            $info['id']
        );
        $this->title = $info['title'];
        $this->url = $info['url'];
        $this->id_page = intval($info['id']);
        $this->pageAccessType = $db->get_lookup_code_by_id(intval($info['id_pageAccessTypes']));
        $this->required_access_level = $info['access_level'];
        $this->page = $info;
        if($info['is_headless']) $this->disable_navigation();
        $this->id_navigation_section = null;
        if($info['id_navigation_section'] != null)
            $this->id_navigation_section = intval($info['id_navigation_section']);
        $this->set_last_user_page();
    }

    /**
     * Set the last unique page that the user visited in his/her SESSION
     */
    private function set_last_user_page()
    {
        $curr_url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (!isset($_SESSION['last_user_page']) && isset($_SERVER['HTTP_REFERER'])) {
            // if the variable is not set assign it
            $_SESSION['last_user_page'] = $_SERVER['HTTP_REFERER'];
        } else if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != $curr_url) {
            // if it is set but the current url is from a new page, reassign the page
            $_SESSION['last_user_page'] = $_SERVER['HTTP_REFERER'];
        }
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
            $router = $this->services->get_router();
            $include_path = $router->get_asset_path($css_include);
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
        /* sort($this->js_includes); */
        foreach($this->js_includes as $js_include)
        {
            $router = $this->services->get_router();
            $include_path = $router->get_asset_path($js_include);
            require __DIR__ . '/tpl_js_include.php';
        }
    }

    /**
     * Add page include files and render the js include directives at hte last postion.
     */
    private function output_js_includes_after()
    {
        foreach($this->js_includes_after as $js_include)
        {
            $router = $this->services->get_router();
            $include_path = $router->get_asset_path($js_include);
            require __DIR__ . '/tpl_js_include.php';
        }
    }

    /**
     * Render warning s at the top of the page.
     */
    private function output_warnings()
    {
        if(DEBUG)
        {
            $alert = new BaseStyleComponent('alert', array(
                "type" => "warning",
                "css" => "mb-0",
                "children" => array(new BaseStyleComponent('plaintext', array(
                    "text" => "Test Mode!"
                )))
            ));
            $alert->output_content();
        }
        $msg = null;
        $date = null;
        $time = null;
        $maintenance_fields = $this->services->get_db()->fetch_page_info(SH_MAINTENANCE);
        if ($maintenance_fields) {
            if (isset($maintenance_fields[PF_MAINTENANCE])) {
                $msg = $maintenance_fields[PF_MAINTENANCE];
            }
            if (isset($maintenance_fields[PF_MAINTENANCE_DATE])) {
                $date = $maintenance_fields[PF_MAINTENANCE_DATE];
            }
            if (isset($maintenance_fields[PF_MAINTENANCE_TIME])) {
                $time = $maintenance_fields[PF_MAINTENANCE_TIME];
            }
        }
        if($msg && $date && $time)
        {
            $msg = str_replace('@date', $date, $msg);
            $msg = str_replace('@time', $time, $msg);
            $alert = new BaseStyleComponent('alert', array(
                "type" => "warning",
                "css" => "mb-0",
                "children" => array(new BaseStyleComponent('markdown', array(
                    "text_md" => $msg,
                )))
            ));
            $alert->output_content();
        }
    }

    private function get_external_css_for_mobile(){
        $path = CSS_SERVER_PATH;
        $extension = 'css';
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
        $css_content = '';
        foreach($files as $file)
        {
            $file_parts = pathinfo($file);
            if($file_parts['extension'] === $extension)
                $css_content .= file_get_contents($path . '/' . $file);
        }
        return $css_content;
    }

    /* Protected Abstract Methods ***********************************************/

    /**
     * Render the content of the page.
     * This function needs to be implemented by the class extending the BasePage.
     */
    abstract protected function output_content();

    /**
     * Render the content of the page.
     * This function needs to be implemented by the class extending the BasePage.
     */
    abstract protected function output_content_mobile();

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
        $login= $this->services->get_login();
        if($this->render_nav) $this->output_component("nav");
        if($this->acl_pass)
            $this->output_content();
        else if($login->is_logged_in())
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
     * Render the content of the page.
     */
    public function output_base_content_mobile()
    {
        $res = [];
        $login= $this->services->get_login();
        if($this->render_nav){ 
            $res['navigation'] = $this->output_component_mobile("nav");
        }
        if($this->acl_pass)
            $res['content'] = $this->output_content_mobile();
        else if($login->is_logged_in())
        {
            $page = new InternalPage($this, "no_access");
            $page->output_content_mobile();
        }
        else
        {
            $page = new InternalPage($this, "no_access_guest");
            $page->output_content_mobile();
        }
        // if($this->render_footer) $this->output_component("footer");
        $res['title'] = $this->title;
        $res['avatar'] = $this->services->get_user_input()->get_avatar($_SESSION['id_user']);
        $res['external_css'] = $this->get_external_css_for_mobile();
        $res['external_css'] = $res['external_css'] . ' ' . $this->get_global_custom_css();
        $res['languages'] = $this->services->get_db()->get_languages();
        $res['redirect_url'] = isset($_SESSION[MOBILE_REDIRECT_URL]) ? $_SESSION[MOBILE_REDIRECT_URL] : false;
        return $res;
    }

    /**
     * Render the meta tags of the page.
     */
    protected function output_meta_tags()
    {
        $description = "";
        $db = $this->services->get_db();
        if (isset($this->page['description'])) {
            // if the page has description assign it
            $description = $this->page['description'];
        } else {
            // get home description and use it
            $home_id = $db->fetch_page_id_by_keyword('home');
            $fields = $db->fetch_pages($home_id, $_SESSION['language']);
            $description = isset($fields['description']) ? $fields['description'] : '';
        }        
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
        $this->css_includes = array_merge($this->css_includes, $component->get_css_includes());
        $this->js_includes = array_merge($this->js_includes, $component->get_js_includes());
        if ($component->get_view() instanceof  StyleView) {
            $this->add_children_js_css($component->get_view()->get_children());
        }
    }

    /**
     * Load recursively all js and css needed for all children
     * @param array $children
     * The children from which we want to load the js and css
     */
    public function add_children_js_css($children)
    {
        foreach ($children as $key => $child) {
            $this->js_includes = array_merge($this->js_includes, $child->get_js_includes());
            $this->css_includes = array_merge($this->css_includes, $child->get_css_includes());
            if ($child->get_view() instanceof  StyleView) {
                $this->add_children_js_css($child->get_view()->get_children());
            }
        }
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

    public function output_component_mobile($key)
    {
        $component = $this->get_component($key);
        if($component != null)
            return $component->output_content_mobile();
    }

    /**
     * Checks whether the current page is a CMS page.
     *
     * @retval bool
     *  true if the current page is a CMS page, false otherwise.
     */
    public function is_cms_page()
    {
        return ($this->services->get_router()->is_active("cms")
            || $this->services->get_router()->is_active("cmsSelect")
            || $this->services->get_router()->is_active("cmsUpdate")
            || $this->services->get_router()->is_active("cmsInsert")
            || $this->services->get_router()->is_active("cmsDelete")
        );

    }

    public function get_global_custom_css(){
        $global_css_page = $this->services->get_db()->fetch_page_info(SH_GLOBAL_CSS);
        return isset($global_css_page[PF_GLOBAL_CUSTOM_CSS]) ? $global_css_page[PF_GLOBAL_CUSTOM_CSS] : '';
    }

    public function output_custom_css(){
        echo $this->get_global_custom_css();        
    }
}
?>
