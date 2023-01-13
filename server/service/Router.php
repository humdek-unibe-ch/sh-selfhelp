<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/ext/AltoRouter.php";
require_once __DIR__ . "/globals_untracked.php";

/**
 * An extension class to the altorouter library.
 */
class Router extends AltoRouter {

    /**
     *  The instance of the service class PageDb.
     */
    private $db;

    /**
     * The router array which holds the name, target, and parameters of a route.
     */
    public $route = NULL;

    /**
     * The current_route of the request
     */
    public $current_route = NULL;

    /**
     * The current keyword
     */
    public $current_keyword;

    /**
     * The constructor which calls the parent constructor.
     *
     * @param instance $db
     *  The instance of the service class PageDb
     * @param string $basePath
     *  The path prefix.
     * @param array $routes
     *  The array of existing routes.
     * @param array $matchTypes
     *  The set of types to match.
     */
    function __construct($db, $basePath, $routes = array(), $matchTypes = array())
    {
        $this->db = $db;
        parent::__construct($routes, $basePath, $matchTypes);
    }

    /**
     * Get the path of an asset such that it matches with the base path.
     *
     * @param string $path
     *  The path to the asset.
     * @retval string
     *  The modified asset path.
     */
    public function get_asset_path( $path ) {
        $path = str_replace($_SERVER['DOCUMENT_ROOT'], "", $path);
        return $this->basePath . $path;
    }

    /**
     * Returns an url given a router keyword. The keyword \#back will generate
     * the url of the last visited page or the home page if the last visited
     * page is the current page or unknown. The keyword \#self points to the
     * current page.
     *
     * @retval string
     *  The generated url.
     */
    public function get_url($url)
    {
        if($url == "") return $url;
        if($url == "#back")
        {
            if(isset($_SERVER['HTTP_REFERER'])
                    && ($_SERVER['HTTP_REFERER'] != $_SERVER['REQUEST_URI']))
            {
                return htmlspecialchars($_SERVER['HTTP_REFERER']);
            }
            return $this->generate("home");
        }
        else if($url == "#last_user_page"){
            return isset($_SESSION['last_user_page']) ? $_SESSION['last_user_page'] : '';
        }
        else if($url == "#self")
            return $_SERVER['REQUEST_URI'];
        else if($url[0] == "#")
        {
            $links = explode('#', substr($url, 1));
            $target = $links[0];
            $names = explode('/', $target);
            $name = $names[0];
            if(!$this->has_route($name))
                return $url;
            $link = "";
            $sql = "SELECT id FROM sections WHERE name = :name";
            if(count($names) === 2)
            {
                $section_id = $this->db->query_db_first($sql,
                    array(":name" => $names[1]));
                if($section_id)
                    $link = $this->generate($name,
                        array('nav' => intval($section_id['id'])));
            }
            else
                $link = $this->generate($name);
            if(count($links) === 2)
            {
                $section_id = $this->db->query_db_first($sql,
                    array(":name" => $links[1]));
                if($section_id)
                    $link .= '#section-' . intval($section_id['id']);
            }
            return $link;
        }
        else if($url[0] == "%")
        {
            return ASSET_PATH . '/' . substr($url, 1);
        }
        else if($url[0] == "|")
        {
            return BASE_PATH . '/' . substr($url, 1);
        }
        else
            return $url;
    }

    /**
     * Check whther the given route is active.
     *
     * @param string $route_name
     *  The name of the route to check.
     * @retval bool
     *  True if the given path is active, false otherwise.
     */
    public function is_active( $route_name )
    {
        if(!$this->current_route){
            $this->current_route = $this->match();
        }        
        // if(!$match){
        //     return false;
        // }
        return ($this->current_route ? $this->current_route['name'] == $route_name : $this->current_route);
    }

    /**
     * Checks whether the given route exists.
     *
     * @param string $name
     *  The name of the route
     * @retval bool
     *  True if the route exists, false otherwise.
     */
    public function has_route($name)
    {
        return isset($this->namedRoutes[$name]);
    }

    /**
     * Updates the route array.
     */
    public function update_route() {
        $this->route = $this->match();
    }

    /**
     * get the keyword from the URL in the browser
     * @retval string
     * return the keyword if found or false if not
     */
    public function get_keyword_from_url()
    {
        if ($this->current_keyword) {
            return $this->current_keyword;
        }
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if (BASE_PATH == '' && count($path) >= 1) {
            $this->current_keyword = $path[1];
        } else if (BASE_PATH != '' && count($path) >= 2) {
            $this->current_keyword = $path[2];
        } else {
            $this->current_keyword = false;
        }
        return $this->current_keyword;
    }

    /**
     * Generates the url of a link, given a router keyword.
     *
     * @param string $key
     *  A router key.
     * @param array $params
     *  The url parameters used to generate the url.
     *
     * @retval string
     *  The generated link url.
     */
    public function get_link_url($key, $params=array())
    {
        if($this->has_route($key))
            return $this->generate($key, $params);
        else
            return "";
    }
}
?>
