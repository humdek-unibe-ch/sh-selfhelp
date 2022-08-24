<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
        foreach($pages as $page)
        {
            $key = $page['keyword'];
            $nav_child = $this->model->get_first_nav_section($page['id_navigation_section']);
            $icon = $this->get_icon($page['icon']);
            if(empty($page['children']))
                $this->output_nav_item($key, $page['title'], $nav_child, $page['is_active'], $icon);
            else
                $this->output_nav_menu($key, $page['title'], $page['children'], $page['is_active'], false, $icon);
        }
    }

    /**
     * Return icon value for web if it exists
     * @param string $icon
     * icon value form cms
     * @retval string or false
     * Return the icon value or false if none set 
     */
    private function get_icon($icon)
    {   
        if(!$icon){
            return false;
        }
        $icons = explode(' ', $icon);
        if (count($icons) > 0) {
            foreach ($icons as $key => $iconValue) {
                if (strpos($iconValue, 'mobile-') === 0) {
                    // not needed for web
                } else {
                    return $iconValue;
                }
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * Render a navigation link, given a keyword and a page name.
     *
     * @param string $key
     *  The identification string of a route.
     * @param string $page_name
     *  The title of the page the link is pointing to.
     * @param int $nav_child
     *  The id of the target navigation section (only relevant for navigation
     *  pages).
     * @param bool $is_active
     *  A flag indicating whether the menu item is currently active.
     * @param string $icon
     * if the menu should show an icon
     */
    private function output_nav_item($key, $page_name, $nav_child=null,
            $is_active=false, $icon='')
    {
        $active = ($is_active) ? "active" : "";
        $params = array();
        if($nav_child !== null)
            $params['nav'] = $nav_child;        
        $url = $this->model->get_link_url($key, $params);
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
     * @param bool $is_active
     *  A flag indicating whether the menu item is currently active.
     * @param bool $right
     *  If set to true the nemu is aligned to the right of the navbar. If set
     *  to false, the menu is left aligned (default).
     * @param string $icon
     * if the menu should show an icon
     */
    private function output_nav_menu($key, $page_name, $children,
            $is_active=false, $right=false, $icon='')
    {
        $align = ($right) ? "dropdown-menu-right" : "";
        $active = ($is_active) ? "active" : "";
        require __DIR__ . "/tpl_nav_menu.php";
    }

    /**
     * Render a menu item, given a keyword and a page name.
     *
     * @param string $key
     *  The identification string of a route.
     * @param string $page_name
     *  The title of the page the link is pointing to.
     * @param int $nav_child
     *  The id of the target navigation section (only relevant for navigation
     *  pages).
     * @param bool $is_active
     *  A flag indicating whether the menu item is currently active.
     * @param string $icon
     * if the menu should show an icon
     */
    private function output_nav_menu_item($key, $page_name, $nav_child,
            $is_active=false, $icon='')
    {
        $active = ($is_active) ? "active" : "";
        $params = array();
        if($nav_child !== null)
            $params['nav'] = $nav_child;
        $url = $this->model->get_link_url($key, $params);
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
        foreach($children as $page)
        {
            $key = $page['keyword'];
            if(empty($page['children']))
            {
                $icon = $this->get_icon($page['icon']);
                $nav_child = $this->model->get_first_nav_section($page['id_navigation_section']);
                $this->output_nav_menu_item($key, $page['title'], $nav_child, $page['is_active'], $icon);
            }
            else{
                $icon = $this->get_icon($page['icon']);
                $this->output_nav_menu($key, $page['title'], $page['children'], $page['is_active'], false, $icon);
            }
        }
    }    

    /**
     * Render the profile menu.
     */
    private function output_profile()
    {
        $profile = $this->model->get_profile();
        $this->output_nav_menu('profile', $profile['title'],
            $profile['children'], $profile['is_active'], true, $profile['avatar']); 
    }

    /**
     * Render icon
     */
    private function output_icon($icon){
        if ($icon && (strpos($icon, '.png') !== false || strpos($icon, '.jpg') !== false)) {
            require __DIR__ . '/tpl_custom_icon.php';
        } else if ($icon) {
            require __DIR__ . '/tpl_icon.php';
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
        $login_is_active = $this->model->get_login_active();
        $login = $this->model->get_login();
        if ($this->model->get_services()->get_user_input()->is_new_ui_enabled()) {
            require __DIR__ . "/../cms/tpl_new_ui/tpl_nav.php";
        }else{
            require __DIR__ . "/tpl_nav.php";
        }        
    }    

    public function output_content_mobile()
    {
        $res = $this->model->get_pages_mobile();
        $home = array(
            'id_navigation_section' => null,
            'title' => $this->model->get_home(),
            'keyword' => 'home',
            'url' => '/home',
            'icon' => 'mobile-home',
            'children' => array(),
            'is_active' => false
        );    
        array_unshift($res, $home);
        foreach ($res as $key => $value) {
            unset($res[$key]['is_active']);
            if (isset($value['children'])) {
                foreach ($value['children'] as $subNavKey => $subNav) {
                    unset($value['children'][$subNavKey]['is_active']);
                }
                $res[$key]['children'] = array_values($value['children']);
            }
        }

        foreach ($res as $arr_key => $page) {
            // get navigation page url corectly
            $key = $page['keyword'];
            $nav_child = $this->model->get_first_nav_section($page['id_navigation_section']);
            if ($nav_child !== null) {
                $params['nav'] = $nav_child;
                $res[$arr_key]['url'] = str_replace($_SERVER['CONTEXT_PREFIX'], '', $this->model->get_link_url($key, $params));
            }
        }

        return $res;
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(
            __DIR__ . "/css/nav.css"
        );
        return parent::get_css_includes($local);
    }
}
?>
