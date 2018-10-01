<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the nested list style component.
 */
class NestedListView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'is_collapsible' (true).
     * If set to true the nested list is collapsible via a chevron.
     * If set to false, the chevron is not rendered.
     */
    private $has_chevron;

    /**
     * DB field 'is_expanded' (false).
     * If set to true the list is expanded by default.
     * If set to false the list is collapsed by default.
     */
    private $is_expanded;

    /**
     * DB field 'id_active' (0).
     * The active id of the list (to be marked as active).
     */
    private $id_active;

    /**
     * DB field 'id_prefix' (empty string).
     * An id prefix that is used if multiple lists are used on the same page.
     */
    private $id_prefix;

    /**
     * DB field 'search_text' (empty string).
     * The default text displayed in the search field. If not set, the search
     * form element will not be rendered.
     */
    private $search_text;

    /**
     * DB field 'title_prefix' (empty string).
     * The text to be rendered as title when the menu is collapsed for smaller
     * screens. If this field is not set or set to the empty string, the menu
     * is not collapsed on smaller screens.
     */
    private $title_collapsed;

    /**
     * DB field 'items' (empty array).
     * A hierarchical array holding the list items
     * An item in the items list must have the following keys:
     *  'id':       The item id (required).
     *  'title':    The title of the item (required).
     *  'children': The children of this item.
     *  'url':      The target url.
     */
    private $items;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->title_collapsed = $this->model->get_db_field("title_prefix");
        $this->id_active = $this->model->get_db_field("id_active", 0);
        $this->is_expanded = $this->model->get_db_field("is_expanded", false);
        $this->has_chevron = $this->model->get_db_field("is_collapsible", true);
        if(!$this->has_chevron) $this->is_expanded = true;
        $this->id_prefix = $this->model->get_db_field("id_prefix");
        $this->search_text = $this->model->get_db_field("search_text");
        $this->items = $this->model->get_db_field("items", array());
    }

    /* Private Methods ********************************************************/

    /**
     * Render the initial chevron symbol or a placeholder.
     *
     * @param bool $is_collapsible
     *  Indicates whether the element can be collapsed or not.
     * @param bool $is_expanded
     *  Indicates whether the element is expanded or not.
     */
    private function output_chevron($is_collapsible, $is_expanded)
    {
        if(!$is_collapsible) return;
        $direction = ($is_expanded) ? "down" : "right";
        require __DIR__ . "/tpl_chevron.php";
    }

    /**
     * Checks whether a child is active.
     *
     * @param array $children
     *  An array of items (see NestedListView::items).
     * @param int $id_active
     *  The id of the curently active item.
     * @retval bool
     *  True if an active item was found, false otherwise.
     */
    private function is_child_active($children, $id_active)
    {
        foreach($children as $index => $item)
        {
            if($id_active == $this->get_id($item['id']))
                return true;
            if($this->is_child_active($item['children'], $id_active))
                return true;
        }
    }

    /**
     * Return the title of the currently active child.
     *
     * @param array $children
     *  An array of items (see NestedListView::items).
     * @param int $id_active
     *  The id of the curently active item.
     * @retval string
     *  The title of the active item or null if no title was found.
     */
    private function get_child_active($children, $id_active)
    {
        foreach($children as $index => $item)
        {
            if($id_active == $this->get_id($item['id']))
                return $item['title'];
            $res = $this->get_child_active($item['children'], $id_active);
            if($res) return $res;
        }
        return null;
    }

    /**
     * Render a list item.
     *
     * @param array $item
     *  An associative array holding item information (see class NestedListView
     *  description).
     */
    private function output_list_item($item)
    {
        if($item == null) return;
        $children = isset($item['children']) ? $item['children'] : array();
        $id = $this->get_id($item['id']);
        $id_html = $this->id_prefix . "-" . $id;

        $is_collapsible = (count($children) > 0 && $this->has_chevron);
        $collapsible = $is_collapsible ? "collapsible" : "";
        $active = "";
        $is_expanded = $this->is_expanded;
        if($this->id_active == $id)
        {
            $active = "active";
            $is_expanded = true;
        }
        if($this->is_child_active($children, $this->id_active))
            $is_expanded = true;
        require __DIR__ . "/tpl_list_item.php";
    }

    /**
     * Render the name of a list item.
     *
     * @param array $item
     *  An associative array holding item information (see class NestedListView
     *  description).
     * @param string $active
     *  The css class string indicating whether an item is currently active or
     *  not
     * @param string $id_html
     *  The unique id string of the item.
     */
    private function output_list_item_name($item, $active, $id_html)
    {
        $margin = ($this->has_chevron) ? "ml-4" : "";
        $name = $item['title'];
        if(isset($item['url']) && $item['url'] != "")
        {
            $url = $item['url'];
            require __DIR__ . "/tpl_link.php";
        }
        else
            require __DIR__ . "/tpl_name.php";

    }

    /**
     * Get the stringified id of a list item.
     *
     * @param mixed $id
     *  The id of an item.
     * @retval string
     *  The stringified id of the item.
     */
    private function get_id($id)
    {
        if(is_array($id))
            return implode("-", $id);
        return $id;
    }

    /**
     * Render the list without collapsible wrapper.
     */
    private function output_list()
    {
        require __DIR__ . "/tpl_list.php";
    }

    /**
     * Render a list of items.
     *
     * @param array $items
     *  an array of items (see class NestedListView description).
     */
    private function output_list_items($items)
    {
        foreach($items as $index => $item)
            $this->output_list_item($item);
    }

    /**
     * Render the collapsable container which holds the child items of an item.
     *
     * @param array $items
     *  an array of items (see class NestedListView description).
     * @param bool $is_expanded
     *  Indicates whether the element is expanded or not.
     */
    private function output_children_container($items, $is_expanded)
    {
        if(count($items) == 0) return;
        $show = ($is_expanded) ? "show" : "";
        require __DIR__ . "/tpl_children_container.php";
    }

    /**
     * Render the search form at the top of the list.
     */
    private function output_search_from()
    {
        if($this->search_text == "") return;
        require __DIR__ . "/tpl_search_form.php";
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
        $local = array(__DIR__ . "/nestedList.css");
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
        $local = array(__DIR__ . "/nestedList.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->title_collapsed != "")
        {
            $title = $this->get_child_active($this->items, $this->id_active);
            if($title) $title = $this->title_collapsed . " - " . $title;
            else $title = $this->title_collapsed;
            require __DIR__ . "/tpl_list_collapsed.php";
        }
        else
            $this->output_list();
    }
}
?>
