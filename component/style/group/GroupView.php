<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the group component.
 */
class GroupView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user component.
     * @param object $controller
     *  The controller instance of the user component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->add_local_component("groups",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "User Groups",
                "children" => array(new BaseStyleComponent("nestedList", array(
                    "items" => $this->model->get_groups(),
                    "id_prefix" => "groups",
                    "has_chevron" => false,
                    "id_active" => 0,
                )))
            ))
        );
        $selected_group = $this->model->get_selected_group();
        $this->add_local_component("group_users",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => true,
                "title" => "Users in Group <code>"
                    . $selected_group['name'] . "</code>",
                "children" => array(
                    new BaseStyleComponent("sortableList", array(
                        "edit" => true,
                        "items" => $this->model->get_selected_group_users(),
                        "insert_target" => "bla",
                        "delete_target" => "bla",
                        "label" => "Add User",
                    ))
                )
            ))
        );
        $this->add_local_component("group_acl",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => true,
                "title" => "Access Rights of Group <code>"
                    . $selected_group['name'] . "</code>",
                "children" => array(
                    new BaseStyleComponent("acl", array(
                        "title" => "Group",
                        "items" => $this->model->get_acl_selected_group()
                    ))
                )
            ))
        );
    }

    /* Private Methods ********************************************************/

    private function output_groups()
    {
        $this->output_local_component("groups");
    }

    private function output_group_acl()
    {
        $this->output_local_component("group_acl");
    }

    private function output_group_users()
    {
        $this->output_local_component("group_users");
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
        $local = array(__DIR__ . "/group.css");
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
        $local = array(__DIR__ . "/group.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_group.php";
    }
}
?>
