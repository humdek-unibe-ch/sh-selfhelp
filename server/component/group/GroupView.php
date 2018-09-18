<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the group select and update component.
 */
class GroupView extends BaseView
{
    /* Private Properties *****************************************************/

    private $selected_group;
    private $mode;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user component.
     */
    public function __construct($model, $controller = null, $mode = "select")
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
        $this->selected_group = $this->model->get_selected_group();
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to update the group ACL.",
                )))
            ))
        );
        $this->add_local_component("alert-success",
            new BaseStyleComponent("alert", array(
                "type" => "success",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Successfully updated the group ACL.",
                )))
            ))
        );
        $this->add_local_component("new-group", new BaseStyleComponent("button",
            array(
                "label" => "Create New Group",
                "url" => $this->model->get_link_url("groupInsert"),
                "type" => "secondary",
                "css" => "d-block mb-3",
            )
        ));
        $this->add_local_component("groups",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Groups",
                "children" => array(new BaseStyleComponent("nestedList", array(
                    "items" => $this->model->get_groups(),
                    "id_prefix" => "groups",
                    "has_hierarchy" => false,
                    "id_active" => $this->model->get_gid(),
                )))
            ))
        );
        $this->add_local_component("group_acl",
            new BaseStyleComponent("card", array(
                "is_expanded" => false,
                "is_collapsible" => true,
                "title" => "ACL",
                "children" => array(new BaseStyleComponent("acl", array(
                    "title" => "Page",
                    "items" => $this->model->get_acl_selected_group()
                )))
            ))
        );
        $url_edit = "";
        if($this->model->can_modify_group_acl())
            $url_edit = $this->model->get_link_url("groupUpdate",
                array('gid' => $this->selected_group['id']));
        $this->add_local_component("group_simple_acl",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "url" => $url_edit,
                "title" => "Group Access Rights",
                "children" => array(new BaseStyleComponent("acl", array(
                    "title" => "Function",
                    "items" => $this->model->get_simple_acl_selected_group()
                )))
            ))
        );
        $this->add_local_component("group_delete",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Delete Group",
                "type" => "danger",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "Deleting a group will remove all data associated to this group. This cannot be undone.",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("button", array(
                        "label" => "Delete Group",
                        "url" => $this->model->get_link_url("groupDelete",
                            array("gid" => $this->selected_group['id'])),
                        "type" => "danger",
                    )),
                )
            ))
        );
        $this->add_local_component("group_simple_acl_form",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "type" => "warning",
                "title" => "Modify Group Access Rights",
                "children" => array(
                    new BaseStyleComponent("form", array(
                        "label" => "Update Group",
                        "url" => $this->model->get_link_url("groupUpdate",
                            array(
                                "gid" => $this->selected_group['id'],
                            )
                        ),
                        "type" => "warning",
                        "url_cancel" => $this->model->get_link_url("groupSelect",
                            array("gid" => $this->selected_group['id'])),
                        "children" => array(
                            new BaseStyleComponent("input", array(
                                "type-input" => "hidden",
                                "name" => "update_acl",
                                "value" => 1,
                                "is_user_input" => false,
                            )),
                            new BaseStyleComponent("acl", array(
                                "title" => "Function",
                                "is_editable" => true,
                                "items" => $this->model->get_simple_acl_selected_group()
                            ))
                        ),
                    ))

                )
            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the alert message.
     */
    private function output_alert()
    {
        if($this->controller && $this->controller->has_failed())
            $this->output_local_component("alert-fail");
        if($this->controller && $this->controller->has_succeeded())
            $this->output_local_component("alert-success");
    }

    /**
     * Render the button to create a new user.
     */
    private function output_button()
    {
        if($this->model->can_create_new_group())
            $this->output_local_component("new-group");
    }

    /**
     * Render the simplified ACL of groups.
     */
    private function output_group_manipulation()
    {
        if($this->mode == "update")
            $this->output_local_component("group_simple_acl_form");
        else
            $this->output_local_component("group_simple_acl");
        if($this->model->can_delete_group())
            $this->output_local_component("group_delete");
    }

    /**
     * Render the list of users.
     */
    private function output_groups()
    {
        $this->output_local_component("groups");
    }

    /**
     * Render the user description or the intro text.
     */
    private function output_main_content()
    {
        if($this->selected_group != null)
            require __DIR__ . "/tpl_group.php";
        else
            require __DIR__ . "/tpl_groups.php";
    }

    /**
     * Render the ACL list.
     */
    private function output_group_acl()
    {
        $this->output_local_component("group_acl");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_main.php";
    }
}
?>
