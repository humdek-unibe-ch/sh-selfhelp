<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the quiz style component.
 */
class NestedListView extends BaseView
{
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
     *  Indicates whether the element has children.
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
     * Render the collapsable attributes of a children container.
     *
     * @param string $id
     *  The id string of the data source.
     * @param bool $has_children
     *  Indicates whether the element has children.
     */
    private function output_collapse($id, $has_children, $is_expanded)
    {
        if(!$has_children) return;
        $is_expanded = ($is_expanded) ? "true" : "false";
        require __DIR__ . "/tpl_collapse.php";
    }

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
     * @param int $id
     *  A numeric identifier of the item. It will be prefixed with a string.
     * @param array $item
     *  An associative array holding item information:
     *   'children' => the children of this item
     *   'name' => the name of the item
     */
    private function output_list_item($item, $first=false)
    {
        if($item == null) return;
        $children = $item['children'];
        $name = $item['title'];
        $url = $item['url'];
        $id = (string)$item['id'];

        $is_expanded = $this->model->get_db_field("is_expanded");
        $id_active = (string)$this->model->get_db_field("id_active");
        $id_prefix = $this->model->get_db_field("id_prefix");

        $has_children = (count($item['children']) > 0);
        $active = "";
        if($id_active === $id)
        {
            $active = "active";
            $is_expanded = true;
        }
        if($this->is_child_active($children, $id_active))
            $is_expanded = true;
        $id = "collapse-item-" . $id_prefix . "-" . $id;
        $item_root = null;
        if(!array_key_exists("root-link", $item) || $item['root-link'])
        {
            $item_root = $item;
            $item_root['children'] = array();
            $item_root['title'] =  $this->model->get_db_field("root_name");
        }
        require __DIR__ . "/tpl_list_item.php";
    }

    /**
     * Render a list of items.
     *
     * @param array $items
     *  An array with key => value pairs where the key is the numeric id of a
     *  list item and the value an associative array, holding the itme content.
     */
    private function output_list_items($items)
    {
        foreach($items as $index => $item)
            $this->output_list_item($item);
    }

    /**
     * Render the collapsable container which holds the child items of an item.
     *
     * @param string $id_root
     *  The id string of the root item.
     * @param array $items
     *  An array with key => value pairs where the key is the numeric id of a
     *  list item and the value an associative array, holding the itme content.
     */
    private function output_children_container($id_root, $item_root, $items, $is_expanded, $first)
    {
        if($first) return;
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
        $title = $this->model->get_db_field("title");
        require __DIR__ . "/tpl_list.php";
    }
}
?>
