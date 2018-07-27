<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the quiz style component.
 */
class NestedListView extends BaseView
{
    private $is_expanded;

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
    }

    /* Private Methods ********************************************************/

    /**
     * Render the initial chevron symbol or a placeholder.
     *
     * @param bool $has_children
     *  Indicates whether the element has children.
     */
    private function output_chevron($has_children)
    {
        if($has_children)
        {
            $direction = ($this->is_expanded) ? "down" : "right";
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
    private function output_collapse($id, $has_children)
    {
        if(!$has_children) return;
        $is_expanded = ($this->is_expanded) ? "true" : "false";
        require __DIR__ . "/tpl_collapse.php";
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
    private function output_list_item($id, $item)
    {
        $id_prefix = $this->model->get_db_field("id_prefix");
        $id = $id_prefix . "-" . $id;
        $has_children = (count($item['children']) > 0);
        $children = $item['children'];
        $name = $item['name'];
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
        foreach($items as $id => $item)
            $this->output_list_item($id, $item);
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
    private function output_children_container($id_root, $items)
    {
        if(count($items) == 0) return;
        $show = ($this->is_expanded) ? "show" : "";
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
        $this->is_expanded = $this->model->get_db_field("is_expanded");
        $items = $this->model->get_db_field("items");
        $search_text = $this->model->get_db_field("search_text");
        require __DIR__ . "/tpl_list.php";
    }
}
?>
