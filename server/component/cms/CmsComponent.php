<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";

/**
 * The cms component.
 */
class CmsComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor. It passes the view and controller instance to the
     * constructor of the parent class.
     *
     * @param object $model
     *  The model instance of the view component.
     * @param object $view
     *  The view instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $view, $controller = null)
    {
        parent::__construct($model, $view, $controller);
    }

    /* Protected Methods ******************************************************/

    /**
     * Checks whether an item is in a hierarchical list of items.
     *
     * @param int $id
     *  The id of the item to check.
     * @param array $items
     *  A list of items.
     */
    protected function is_in_list($id, $items)
    {
        foreach($items as $item)
        {
            if($this->is_in_list($id, $item['children']))
                return true;
            if($item['id'] == $id) return true;
        }
        return false;
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function to deny access on invalid pages and
     * sections.
     *
     * @retval bool
     *  True if the user the page or section exists, false otherwise
     */
    public function has_access($skip_ids = false)
    {
        $params = $this->model->get_current_url_params();
        $pages = $this->model->get_pages();
        $sections = $this->model->get_page_sections();
        $nav_sections = array();
        if($params["type"] == RELATION_SECTION_NAV || $params["type"] == RELATION_PAGE_NAV)
            $nav_sections = $this->model->get_navigation_hierarchy();
        if(!$skip_ids
            && ((($params['pid'] != null && count($pages) > 0)
                && !$this->is_in_list($params['pid'], $pages))
            || (($params['ssid'] != null)
                && !$this->is_in_list($params['ssid'], $sections))
            || ($params['sid'] != null
                && !$this->is_in_list($params['sid'], $sections))
            || ($params['did'] != null
                && !$this->is_in_list($params['did'], $sections)
                && !$this->is_in_list($params['did'], $nav_sections))))
        {
            return false;
        }
        return parent::has_access();
    }
}
?>
