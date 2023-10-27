<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the accordion list style component.
 * This style component renders a accordion list as it is proposed by bootstrap.
 */
class AccordionListView extends StyleView
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

    /**
     * DB field 'id_prefix' ("accordion").
     * A string to be prepended to the ids of the accordion items to prevent
     * interference between multiple lists.
     */
    private $id_prefix;

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
        $this->id_prefix = $this->model->get_db_field("id_prefix", "accordion");
    }

    /* Private Methods ********************************************************/

    /**
     * Checks whether the current item id belongs to the root section in
     * order to decide whether a root item element needs to be expanded.
     *
     * @param array $root
     *  A root navigation array.
     * @retval bool
     *  true if the current item id belongs to the root item, false
     *  otherwise.
     */
    private function is_child_active($root)
    {
        if(!isset($root['id'])) return false;
        if($root['id'] == $this->id_active)
            return true;
        if(isset($root['children']))
            foreach($root['children'] as $child)
                if($this->is_child_active($child))
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
            if(!isset($child['id']) || !isset($child['title']))
                continue;
            $children = array();
            $url = "";
            if(isset($child['children'])) $children = $child['children'];
            if(isset($child['url'])) $url = $child['url'];
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
            if(!isset($child['id']) || !isset($child['title']))
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
        if($child['id'] == $this->id_active)
            $active = "active";
        if($first)
        {
            if($this->root_name === "" || !isset($child['url'])
                || $child['url'] === "") return;
            $child['title'] = $this->root_name;
            $child['children'] = array();
        }
        require __DIR__ . "/tpl_item.php";
    }

    /**
     * Render the item label
     *
     * @param array $child
     *  An associative array holding the fields of a navigation item.
     */
    private function output_label($child)
    {
        if(isset($child['url']) && $child['url'] !== "")
            require __DIR__ . "/tpl_link.php";
        else
            echo $child['title'];
    }

    /**
     * Render the root link symbol
     *
     * @param string $url
     *  The url of the root item link.
     */
    private function output_link_symbol($url)
    {
        if($this->root_name === "" && $url !== "")
            require __DIR__ . "/tpl_link_symbol.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_accordion_list.php";
    }

}
?>
