<?php
require_once __DIR__ . "/../IView.php";

/**
 * The view class of the navigation component.
 */
class NavView implements IView
{
    /* Private Properties *****************************************************/

    private $router;
    private $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($router, $model)
    {
        $this->router = $router;
        $this->model = $model;
    }

    /* Private Methods ********************************************************/

    /**
     * Determines wheter a link is active or not and returns a css class
     * accordingly.
     *
     * @param string $route_name
     *  The identification string of a route.
     * @retval string
     *  Returns "active" if the route is the active route, an empty string
     *  otherwise.
     */
    private function get_active_css($route_name)
    {
        if( $this->router->is_active( $route_name ) )
            return "active";
        return '';
    }

    /**
     * Render all navigation links.
     */
    private function output_nav_items()
    {
        $pages = $this->model->get_pages();
        foreach($pages as $key => $page)
        {
            if(empty($page['children']))
                $this->output_nav_item($key, $page['title']);
            else
                $this->output_nav_menu($key, $page['title'], $page['children']);
        }
    }

    /**
     * Render a navigation link, given a keyword and a page name.
     *
     * @param string $key
     *  The identification string of a route.
     * @param string $page_name
     *  The title of the page the link is pointing to.
     */
    private function output_nav_item($key, $page_name)
    {
        require __DIR__ . "/tpl_nav_item.php";
    }

    /**
     * Render a navigation menu, given a keyword and a page name.
     *
     * @param string $key
     *  The identification string of a route.
     * @param string $page_name
     *  The title of the page the link is pointing to.
     */
    private function output_nav_menu($key, $page_name, $children, $right=false)
    {
        $align = "";
        if($right) $align = "dropdown-menu-right";
        require __DIR__ . "/tpl_nav_menu.php";
    }

    /**
     * Render a menu item, given a keyword and a page name.
     *
     * @param string $key
     *  The identification string of a route.
     * @param string $page_name
     *  The title of the page the link is pointing to.
     */
    private function output_nav_menu_item($key, $page_name)
    {
        require __DIR__ . "/tpl_nav_menu_item.php";
    }

    /**
     * Render a menu item, given a keyword and a page name.
     *
     * @param string $key
     *  The identification string of a route.
     * @param string $page_name
     *  The title of the page the link is pointing to.
     */
    private function output_nav_menu_items($children)
    {
        foreach($children as $key => $page)
        {
            if(empty($page['children']))
                $this->output_nav_menu_item($key, $page['title']);
            else
                $this->output_nav_menu($key, $page['title'], $page['children']);
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the navigation view.
     */
    public function output_content()
    {
        $home = $this->model->get_home();
        $login = $this->model->get_login();
        $profile = $this->model->get_profile();
        require __DIR__ . "/tpl_nav.php";
    }
}
?>
