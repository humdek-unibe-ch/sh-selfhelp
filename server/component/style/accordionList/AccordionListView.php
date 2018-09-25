<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the accordion list style component.
 */
class AccordionListView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'id_active' (0).
     * The active id of the list (to be marked as active).
     */
    private $id_active;

    /**
     * DB field 'title_prefix' (empty string).
     * A prefix that will be appended to the title of each root item. This is
     * omitted if the field is not set.
     */
    private $title_prefix;

    /**
     * DB field 'items' (empty array).
     * A hierarchical array holding the list items
     * An item in the items list must have the following keys:
     *  'id':       The item id.
     *  'title':    The title of the item.
     *  'children': The children of this item.
     *  'url':      The target url.
     */
    private $items;

    /**
     * DB field 'label_root' (empty string).
     * As the root item is expandable, it cannot be clicked itself. Hence, in
     * order to show the content of the root a new item is intruduced. This item
     * has the name that is provided by this field.
     */
    private $root_name;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->id_active = $this->model->get_db_field("id_active", 0);
        $this->title_prefix = $this->model->get_db_field("title_prefix");
        $this->items = $this->model->get_db_field("items", array());
        $this->root_name = $this->model->get_db_field("label_root");
    }

    /* Private Methods ********************************************************/

    /**
     * Checks whether the current item id belongs to the root section in
     * order to decide whether a root item element needs to be expanded.
     *
     * @param array $child_root
     *  A root navigation array.
     *
     * @return bool
     *  true if the current item id belongs to the root item, false
     *  otherwise.
     */
    private function is_child_active($child_root)
    {
        $id = $this->id_active;
        if($child_root['id'] == $id)
            return true;
        foreach($child_root['children'] as $child)
            if($id == $child['id'] || $this->is_child_active($child, $id))
                return true;
        return false;
    }

    /**
     * Renders all root navigation items.
     */
    private function output_root_children()
    {
        foreach($this->items as $index => $child)
        {
            if(!isset($child['id']) || !isset($child['url']) || !isset($child['title']))
                continue;
            if(!isset($child['children'])) $child['children'] = array();
            $active = "";
            if($this->is_child_active($child))
                $active = "show";
            require __DIR__ . "/tpl_root_item.php";
        }
    }

    /**
     * Renders the title prefix
     */
    private function output_title_prefix($index)
    {
        if($this->title_prefix == "") return;
        $number = $index + 1;
        require __DIR__ . "/tpl_title_prefix.php";
    }

    /**
     * Renders all child navigation items.
     *
     * @param array $children
     *  An array hodling the children to be rendered.
     */
    private function output_nav_children($children)
    {
        foreach($children as $index => $child)
        {
            if(!isset($child['id']) || !isset($child['url']) || !isset($child['title']))
                continue;
            $this->output_child($child);
        }
    }

    /**
     * Renders a navigation item.
     *
     * @param array $child
     *  An associative array holding the fields of a navigation item.
     * @param bool $first
     *  A flag, indicating whether the item to be rendered is the first item
     *  (which corresponds to the root navigation item).
     */
    private function output_child($child, $first=false)
    {
        if(!isset($child['children'])) $child['children'] = array();
        $active = "";
        if($child['id'] === $this->id_active)
            $active = "active";
        if($first)
        {
            if($this->root_name === "") return;
            $child['title'] = $this->root_name;
            $child['children'] = array();
        }
        require __DIR__ . "/tpl_item.php";
    }

    private function output_link($url)
    {
        if($this->root_name === "")
            require __DIR__ . "/tpl_link.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(__DIR__ . "/accordionList.js");
        return parent::get_js_includes($local);
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
        $local = array(__DIR__ . "/accordionList.css");
        return parent::get_css_includes($local);
    }

    /**
     * Render the login view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_accordion_list.php";
    }
}
?>
