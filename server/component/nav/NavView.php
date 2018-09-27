<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the navigation component.
 */
class NavView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Private Methods ********************************************************/

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
        $active = ($this->model->is_link_active($key)) ? "active" : "";
        $url = $this->model->get_link_url($key);
        require __DIR__ . "/tpl_nav_item.php";
    }

    /**
     * Render a navigation menu, given a keyword and a page name.
     *
     * @param string $key
     *  The identification string of a route.
     * @param string $page_name
     *  The title of the page the link is pointing to.
     * @param array $children
     *  An array of page items (see NavModel::prepare_pages).
     * @param bool $right
     *  If set to true the nemu is aligned to the right of the navbar. If set
     *  to false, the menu is left aligned (default).
     */
    private function output_nav_menu($key, $page_name, $children, $right=false)
    {
        $align = ($right) ? "dropdown-menu-right" : "";
        $active = ($this->model->is_link_active($key)) ? "active" : "";
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
        $active = ($this->model->is_link_active($key)) ? "active" : "";
        $url = $this->model->get_link_url($key);
        require __DIR__ . "/tpl_nav_menu_item.php";
    }

    /**
     * Render a menu item, given a keyword and a page name.
     *
     * @param array $children
     *  An array of page items (see NavModel::prepare_pages).
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
        $home_url = $this->model->get_link_url("home");
        $home = $this->model->get_home();
        $login = $this->model->get_login();
        $profile = $this->model->get_profile();
        $profile_title = array_key_exists("title", $profile) ?
            $profile["title"] : "";
        $profile_children = array_key_exists("children", $profile) ?
            $profile["children"] : array();
        require __DIR__ . "/tpl_nav.php";
    }
}
?>
