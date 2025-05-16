<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";
require_once __DIR__ . "/../style/StyleComponent.php";

/**
 * The insert view class of the cms component.
 */
class CmsInsertView extends BaseView
{

    /**
     * The array of initial position values of the existing pages. This value
     * will be used when changing the order of pages with javascript.
     */
    private $position_value;

    /**
     * The list of pages at the current level.
     */
    private $pages;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the cms component.
     * @param object $controller
     *  The controller instance of the cms component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->position_value = "";
        $this->pages = $this->model->get_pages_header(
            $this->model->get_active_page_id());
        foreach($this->pages as $idx => $page)
        {
            $this->position_value .= (string)($idx * 10) . ",";
            $this->pages[$idx]["css"] = "fixed text-body-secondary";
        }

        $this->pages[] = array("id" => "new", "title" => "New Page");
        $this->position_value .= (string)(count($this->pages) * 10);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
    }

    /**
     * Render the sortable list to position the page in the header.
     */
    private function output_page_order()
    {
        $list = new BaseStyleComponent("sortableList", array(
            "is_sortable" => true,
            "is_editable" => true,
            "items" => $this->pages,
        ));
        $list->output_content();
    }

    /**
     * Render select with all the page access types.
     */
    private function output_page_access_type()
    {
        $items = $this->get_lookups(pageAccessTypes);
        $access_types = new BaseStyleComponent("select", array(
            "label" => "Page Access Type:",
            "value" => $this->model->get_services()->get_db()->get_lookup_id_by_code(pageAccessTypes, pageAccessTypes_mobile_and_web),
            "css" => "w-100",
            "is_required" => true,
            "name" => "id_pageAccessTypes",
            "items" => $items,
        ));
        $access_types->output_content();
    }
    
    /**
     * Get action types from the lookups table.
     * 
     * @return array
     *  An array of action types with their IDs and values, ordered with sections and navigation first.
     */
    private function get_action_types()
    {
        $sql = "SELECT id, lookup_code, lookup_value FROM lookups WHERE type_code = 'actions'";
        $types = $this->model->get_services()->get_db()->query_db($sql, array());
        
        // Define the preferred order
        $preferred_order = [PAGE_ACTION_SECTIONS, PAGE_ACTION_NAVIGATION];
        
        // Sort the array to put sections and navigation first
        usort($types, function($a, $b) use ($preferred_order) {
            $a_index = array_search($a['lookup_code'], $preferred_order);
            $b_index = array_search($b['lookup_code'], $preferred_order);
            
            // If both are in preferred order, sort by their position in preferred_order
            if ($a_index !== false && $b_index !== false) {
                return $a_index - $b_index;
            }
            
            // If only a is in preferred order, a comes first
            if ($a_index !== false) {
                return -1;
            }
            
            // If only b is in preferred order, b comes first
            if ($b_index !== false) {
                return 1;
            }
            
            // If neither is in preferred order, maintain original order
            return 0;
        });
        
        return $types;
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/new_page.css");
        return parent::get_css_includes($local);
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(__DIR__ . "/new_page.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $name = $this->controller->get_new_page_name();
            $url = $this->model->get_link_url("cmsUpdate",
                array(
                    "pid" => $this->controller->get_new_pid(),
                    "mode" => "update",
                    "type" => "prop",
                ));
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $action_url = $this->model->get_link_url("cmsInsert",
                array("pid" => $this->model->get_active_page_id()));
            $cancel_url = $this->model->get_link_url("cmsSelect",
                array("pid" => $this->model->get_active_page_id()));
            require __DIR__ . "/tpl_cms_insert.php";
        }
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
