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
                $section_id = $this->db->query_db_first($sql, array(":name" => $names[1]));
                if ($section_id) {
                    $link = $this->generate(
                        $name,
                        array('nav' => intval($section_id['id']))
                    );
                } else {
                    $link = $this->generate($name) . '/' .  $names[1];
                }
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
        if (isset($_SERVER['REQUEST_URI'])) {
            $path = explode('/', $_SERVER['REQUEST_URI']);
            if (BASE_PATH == '' && count($path) >= 1) {
                $this->current_keyword = $path[1];
            } else if (BASE_PATH != '' && count($path) >= 2) {
                $this->current_keyword = $path[2];
            } else {
                $this->current_keyword = false;
            }
            return $this->current_keyword;
        } else {
            // when a condition is used in cronjob and needs to calculate the keyword, there is no keyword
            return 'no server requests';
        }
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

    /**
     * Get a route param by name
     * @param string $param_name
     * The name of the param that we search for
     * @return string || false
     * Return the value of the param if it is found or false if it is not
     */
    public function get_param_by_name($param_name)
    {
        if (isset($this->route['params']) && isset($this->route['params'][$param_name])) {
            return $this->route['params'][$param_name];
        } else {
            return false;
        }
    }

    /**
     * Log user activity for a page request
     * @param int $debug_start_time
     * The timestamp when te request is started. We use it to calculate how much time t was needed for the request to be executed
     * @param bool $mobile
     * Is the request from a mobile app. The default is false
     */
    public function log_user_activity($debug_start_time, $mobile = false){
        $sql = "SELECT * FROM pages WHERE id_type = :id AND keyword = :key";
        if ($this->db->query_db_first(
            $sql,
            array(":id" => EXPERIMENT_PAGE_ID, ":key" => $this->route['name'])
        )) {
            //if transaction logs work as expected this should be removed
            $this->db->insert("user_activity", array(
                "id_users" => $_SESSION['id_user'],
                "url" => $_SERVER['REQUEST_URI'],
                "exec_time" => (microtime(true) - $debug_start_time),
                "mobile" => (int)$mobile
            ));
        } else {
            $this->db->insert("user_activity", array(
                "id_users" => $_SESSION['id_user'],
                "url" => $_SERVER['REQUEST_URI'],
                "id_type" => 2,
                "exec_time" => (microtime(true) - $debug_start_time),
                "keyword" => $this->route['name'],
                "params" => json_encode($this->route['params']),
                "mobile" => (int)$mobile
            ));
        }
    }

    /**
     * Get all sensible pages that we want to check if multiple people are editing them at the same time
     * @return array
     * return the keywords of the pages in an array
     */
    public function get_sensible_pages()
    {
        return ['cmsUpdate', 'moduleFormsAction'];
    }

    /**
     * For sensible pages - check if anyone else is working on this page in the last 15 minutes and it is still on the page
     * @return array
     * Return all users that works on the same page
     */
    public function get_other_users_editing_this_page()
    {
        $sensible_pages = $this->get_sensible_pages();
        if (in_array($this->route['name'], $sensible_pages)) {
            // check if anyone else is working on this page in the last 15 minutes and it is still on the page
            $res = array();
            $sql = "SELECT last_requests.*, u.`name` AS user_name, u.email
                    FROM (SELECT *, JSON_UNQUOTE(JSON_EXTRACT(params, '$.pid')) AS id_pages
                    FROM user_activity
                    WHERE `timestamp` >= NOW() - INTERVAL 15 MINUTE
                    AND NOT keyword LIKE 'ajax\_%'
                    AND (id_users, id) IN (
                        SELECT id_users, MAX(id)
                        FROM user_activity
                        WHERE timestamp >= NOW() - INTERVAL 15 MINUTE
                        AND NOT keyword LIKE 'ajax\_%'
                        GROUP BY id_users
                    )
                    ORDER BY id DESC) AS last_requests
                    INNER JOIN users u ON (last_requests.id_users = u.id)
                    WHERE keyword = :keyword";
            if ($this->route['name'] == 'cmsUpdate') {
                // this page is special and we have to check only the page param, and not the current section. 
                $sql = $sql . ' AND id_pages = :id_pages';
                $res = $this->db->query_db($sql, array(":keyword" => $this->route['name'], ":id_pages" => $this->route['params']['pid']));
            } else {
                $sql = $sql . ' AND params = :params';
                $res = $this->db->query_db($sql, array(":keyword" => $this->route['name'], ":params" => json_encode($this->route['params'])));
            }
            if ($res && count($res) > 1) {
                // more than 1 user is editing the page
                return $res;
            } else if ($res && count($res) == 1) {
                // one user check is this user is the same as who checks
                if ($res[0]['id_users'] == $_SESSION['id_user']) {
                    return false;
                }
                return $res;
            }
        } else {
            return false;
        }
    }
}
?>
