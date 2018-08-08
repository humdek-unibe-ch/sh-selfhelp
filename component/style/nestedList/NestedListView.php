<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the nested list style component.
 * This style requires the following fields:
 *  'id_active':
 *   the active id of the list (to be marked as active).
 *  'id_prefix':
 *   an id prefix that is used if multiple lists are used on the same page.
 *  'is_expanded':
 *   defines whether the items are expanded by default.
 *  'items':
 *   a hierarchical array holding the list items
 *  'search_text':
 *   the default text displayed in the search field.
 *
 * An item in the items list must have the following keys:
 *  'id':
 *   the item id
 *  'title':
 *   the title of the item
 *  'children':
 *   the children of this item
 *  'url':
 *   the target url
 */
class NestedListView extends BaseView
{
    /* Private Properties *****************************************************/

    private $is_expanded;
    private $id_active;

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
        $this->id_active = 0;
        $this->is_expanded = false;
    }

    /* Private Methods ********************************************************/

    /**
     * Render the initial chevron symbol or a placeholder.
     *
     * @param bool $has_children
     *  Indicates whether the element has children or not.
     * @param bool $is_expanded
     *  Indicates whether the element is expanded or not.
     */
    private function output_chevron($has_children, $is_expanded)
    {
        if($has_children)
        {
            $direction = ($is_expanded) ? "down" : "right";
            require __DIR__ . "/tpl_chevron.php";
        }
        else
            require __DIR__ . "/tpl_chevron_placeholder.php";
    }

    /**
     * Checks whether a child is active.
     *
     * @param array $children
     *  An array of items (see class NestedListView description).
     * @param int $id_active
     *  The id of the curently active item.
     */
    private function is_child_active($children, $id_active)
    {
        foreach($children as $index => $item)
        {
            if($id_active == $item['id'])
                return true;
            if($this->is_child_active($item['children'], $id_active))
                return true;
        }
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
        $children = $item['children'];
        $id = $item['id'];
        $id_html = $this->model->get_db_field("id_prefix") . "-" . $id;

        $is_expanded = $this->model->get_db_field("is_expanded");
        $id_active = $this->model->get_db_field("id_active");

        $has_children = (count($item['children']) > 0);
        $collapsible = $has_children ? "collapsible" : "";
        $active = "";
        if($id_active === $id)
        {
            $active = "bg-primary text-white";
            $is_expanded = true;
        }
        if($this->is_child_active($children, $id_active))
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
        $name = $item['title'];
        if($item['url'] != "")
        {
            $url = $item['url'];
            require __DIR__ . "/tpl_link.php";
        }
        else
            require __DIR__ . "/tpl_name.php";

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
        $items = $this->model->get_db_field("items");
        $search_text = $this->model->get_db_field("search_text");
        require __DIR__ . "/tpl_list.php";
    }
}
?>
