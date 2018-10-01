<?php
require_once __DIR__ . "/ext/AltoRouter.php";

/**
 * An extension class to the altorouter library.
 */
class Router extends AltoRouter {

    /**
     * The router array which holds the name, target, and parameters of a route.
     */
    public $route = NULL;

    /**
     * The constructor which calls the parent constructor.
     *
     * @param array $routes
     *  The array of existing routes.
     * @param string $basePath
     *  The path prefix.
     * @param array $matchTypes
     *  The set of types to match.
     */
    function __construct( $routes = array(), $basePath = '',
        $matchTypes = array() ) {
        parent::__construct( $routes, $basePath, $matchTypes );
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
     * Check whther the given route is active.
     *
     * @param string $route_name
     *  The name of the route to check.
     * @retval bool
     *  True if the given path is active, false otherwise.
     */
    public function is_active( $route_name )
    {
        $match = $this->match();
        return ($match['name'] == $route_name);
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
}
?>
