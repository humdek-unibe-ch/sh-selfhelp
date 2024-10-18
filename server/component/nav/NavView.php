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
        foreach ($pages as $page) {
            $nav_child = $this->model->get_first_nav_section($page['id_navigation_section']);
            if (empty($page['children'])) {
                $this->output_nav_item($page, $nav_child);
            } else {
                $this->output_nav_menu($page, false);
            }
        }
    }

    /**
     * Return icon value for web if it exists
     * @param string $icon
     * icon value form cms
     * @param boolean $mobile
     * By default is false, if true then returns the icon for mobile
     * @return string or false
     * Return the icon value or false if none set 
     */
    private function get_icon($icon, $mobile = false)
    {
        if (!$icon) {
            return false;
        }
        $icons = explode(' ', $icon);
        if (count($icons) > 0) {
            foreach ($icons as $key => $iconValue) {
                if (strpos($iconValue, 'mobile-') === 0) {
                    // not needed for web
                    if ($mobile) {
                        return $iconValue;
                    }
                } else {
                    if (!$mobile) {
                        return $iconValue;
                    }
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
     * @param string $page
     *  The page info.
     * @param int $nav_child
     *  The id of the target navigation section (only relevant for navigation
     *  pages).
     */
    private function output_nav_item($page, $nav_child=null)
    {
        $active = (isset($page['is_active']) && $page['is_active']) ? "active" : "";
        $page_name = $page['title'];
        $params = array();
        if($nav_child !== null)
            $params['nav'] = $nav_child;        
        $icon = $this->get_icon($page['icon']);
        $url = ($page['action'] == PAGE_ACTION_BACKEND && $page['id_type'] != 1 ? $this->model->get_cms_item_url($page['id']) :  $this->model->get_link_url($page['keyword'], $params));
        require __DIR__ . "/tpl_nav_item.php";
    }

    /**
     *  Render login link
     */
    private function output_login(){
        $this->output_nav_item($this->model->get_services()->get_db()->fetch_page_info('login'), null);
    }

    /**
     * Render a navigation menu, given a keyword and a page name.
     *
     * @param string $page
     *  The page info
     * @param bool $right
     *  If set to true the nemu is aligned to the right of the navbar. If set
     *  to false, the menu is left aligned (default).
     */
    private function output_nav_menu($page, $right = false)
    {
        $page_name = $page['title'];
        $children = $page['children'];
        $is_active = $page['is_active'] ?? false;
        $align = ($right) ? "dropdown-menu-end" : "";
        $active = ($is_active) ? "active" : "";
        $icon = $this->get_icon($page['icon']);
        require __DIR__ . "/tpl_nav_menu.php";
    }

    /**
     * Render a menu item, given a keyword and a page name.
     *
     * @param array $page
     *  The page info
     * @param int $nav_child
     *  The id of the target navigation section (only relevant for navigation
     *  pages).
     */
    private function output_nav_menu_item($page, $nav_child)
    {
        $active = (isset($page['is_active']) && $page['is_active']) ? "active" : "";
        $page_name = $page['title'];
        $params = array();
        if($nav_child !== null)
            $params['nav'] = $nav_child;
        $icon = $this->get_icon($page['icon']);
        $url = ($page['action'] == PAGE_ACTION_BACKEND && $page['id_type'] != 1 ? $this->model->get_cms_item_url($page['id']) :  $this->model->get_link_url($page['keyword'], $params));
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
            if(empty($page['children']))
            {
                $nav_child = $this->model->get_first_nav_section($page['id_navigation_section']);
                $this->output_nav_menu_item($page, $nav_child);
            }
            else{
                $this->output_nav_menu($page, false);
            }
        }
    }    

    /**
     * Render the profile menu.
     */
    private function output_profile()
    {
        $profile = $this->model->get_profile();
        $profile['icon'] = $profile['avatar'] ?? '';
        $this->output_nav_menu($profile, true); 
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
        $home_page = $this->model->get_home();
        $icon = $this->get_icon($home_page['icon'], true);
        $home = array(
            'id_navigation_section' => null,
            'title' => $home_page['title'],
            'keyword' => 'home',
            'url' => '/home',
            'icon' =>  $icon != '' ? $icon : 'mobile-home',
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
