<?php
require_once __DIR__ . "/../../IView.php";

/**
 * The view class of the sessions navigation component.
 */
class SessionsNavView implements IView
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
     * Checks whether the current session id belongs to the root section in
     * order to decide whether a root session element needs to be expanded.
     *
     * @param array $child_root
     *  A root session array.
     *
     * @return bool
     *  true if the current session id belongs to the root session, false
     *  otherwise.
     */
    private function is_child_active($child_root)
    {
        $id = $this->model->get_current_session_id();
        if($child_root['id'] == $id)
            return true;
        foreach($child_root['children'] as $child)
            if($id == $child['id'] || $this->is_child_active($child, $id))
                return true;
        return false;
    }

    /**
     * Render all root session items.
     */
    private function output_root_children()
    {
        $item_label = $this->model->get_item_label();
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
     * Render all child session items.
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
     * Render a session item.
     *
     * @param array $child
     *  An associative array holding the fields of a session item.
     * @param bool $first
     *  A flag, indicating whether the item to be rendered is the first item,
     *  which corresponds to the root session.
     */
    private function output_child($child, $first=false)
    {
        $active = "";
        if($child['id'] == $this->model->get_current_session_id())
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
        require __DIR__ . "/tpl_sessionsNav.php";
    }
}
?>
