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
     * Render the chat link.
     */
    private function output_nav_chat()
    {
        $key = '';
        if ($this->model->has_access_to_chat('chatTherapist')) {
            $key = 'chatTherapist';
        } else if ($this->model->has_access_to_chat('chatSubject')) {
            $key = 'chatSubject';
        } else {
            return;
        }
        $active = ($this->model->is_link_active($key)) ? "active" : "";
        $group =  $this->model->get_chat_first_chat_group();
        if (!$group) {
            // if there is no chat group do not show
            return;
        }
        $url = $this->model->get_link_url($key, array("gid" => intval($group['id_groups'])));
        require __DIR__ . '/tpl_chat.php';
    }

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
            if(empty($page['children']))
                $this->output_nav_item($key, $page['title'], $nav_child, $page['is_active']);
            else
                $this->output_nav_menu($key, $page['title'], $page['children'], $page['is_active']);
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
     */
    private function output_nav_item($key, $page_name, $nav_child=null,
            $is_active=false)
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
     */
    private function output_nav_menu($key, $page_name, $children,
            $is_active=false, $right=false)
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
     */
    private function output_nav_menu_item($key, $page_name, $nav_child,
            $is_active=false)
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
                $nav_child = $this->model->get_first_nav_section($page['id_navigation_section']);
                $this->output_nav_menu_item($key, $page['title'], $nav_child, $page['is_active']);
            }
            else
                $this->output_nav_menu($key, $page['title'], $page['children'], $page['is_active']);
        }
    }

    /**
     * Render the pill indicating new messages.
     */
    private function output_new_messages()
    {
        $count = $this->model->get_new_message_count();
        if($count)
            require __DIR__ .'/tpl_new_messages.php';
    }

    /**
     * Render the profile menu.
     */
    private function output_profile()
    {
        $profile = $this->model->get_profile();
        $this->output_nav_menu('profile', $profile['title'],
            $profile['children'], $profile['is_active'], true);
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
        require __DIR__ . "/tpl_nav.php";
    }

    public function output_content_mobile()
    {
        return $this->model->get_pages();
    }
}
?>
