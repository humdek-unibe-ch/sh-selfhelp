<?php
require_once __DIR__ . "/../IView.php";

/**
 * The view class of the section navigation component.
 */
class NavSectionView implements IView
{
    /* Private Properties *****************************************************/

    private $router;
    private $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($router, $model)
    {
        $this->router = $router;
        $this->model = $model;
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
        $id = $this->model->get_current_id();
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
        $item_label = $this->model->get_item_prefix();
        $children = $this->model->get_children();
        foreach($children as $index => $child)
        {
            $active = "";
            if($this->is_child_active($child))
                $active = "show";
            require __DIR__ . "/tpl_root_item.php";
        }
    }

    /**
     * Renders all child navigation items.
     *
     * @param array $children
     *  An array hodling the session children to be rendered.
     */
    private function output_children($children)
    {
        foreach($children as $index => $child)
            $this->output_child($child);
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
        $active = "";
        if($child['id'] == $this->model->get_current_id())
            $active = "active";
        $child['url'] = $this->router->generate("session",
            array("id" => intval($child['id'])));
        if($first)
        {
            $child['title'] = "Intro";
            $child['children'] = array();
        }
        require __DIR__ . "/tpl_item.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_navSection.php";
    }
}
?>
