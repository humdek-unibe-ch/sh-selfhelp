<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the user component.
 */
class UserView extends BaseView
{
    /* Private Properties *****************************************************/

    private $selected_user;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->selected_user = $this->model->get_selected_user();
        $this->add_local_component("new_user", new BaseStyleComponent("button",
            array(
                "label" => "Create New User",
                "url" => $this->model->get_link_url("userInsert"),
                "type" => "secondary",
                "css" => "d-block mb-3",
            )
        ));
        $this->add_local_component("users",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Registered Users",
                "children" => array(new BaseStyleComponent("nestedList", array(
                    "items" => $this->model->get_users(),
                    "id_prefix" => "users",
                    "has_chevron" => false,
                    "id_active" => 0,
                )))
            ))
        );
        $this->add_local_component("user_blocking",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Block User",
                "type" => "warning",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "Blocking a user will set the user state to blocked. This prevents a user from logging in to the platform.",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("form", array(
                        "label" => "Block User",
                        "url" => $this->model->get_link_url("userUpdate",
                            array("uid" => $this->selected_user['id'],
                                "mode" => "block")),
                        "type" => "warning",
                    )),
                )
            ))
        );
        $this->add_local_component("user_unblocking",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Unblock User",
                "type" => "warning",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "Unblocking a user will revert the state of the user to what it was befor the blocking took place.",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("button", array(
                        "label" => "Unblock User",
                        "url" => $this->model->get_link_url("userUpdate",
                            array("uid" => $this->selected_user['id'],
                                "mode" => "unblock")),
                        "type" => "warning",
                    )),
                )
            ))
        );
        $this->add_local_component("user_delete",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Delete User",
                "type" => "danger",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "Deleting a user will remove all data associated to this user. This cannot be undone.",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("button", array(
                        "label" => "Delete User",
                        "url" => $this->model->get_link_url("userDelete",
                            array("uid" => $this->selected_user['id'])),
                        "type" => "danger",
                    )),
                )
            ))
        );
        $this->add_local_component("user_groups",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "User Groups",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "When assigned to a user, groups provide the user with a predefined set of access rights.",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("sortableList", array(
                        "edit" => $this->model->can_modify_user(),
                        "items" => $this->model->get_selected_user_groups(),
                        "insert_target" => $this->model->get_link_url(
                            "userUpdate",
                            array(
                                "uid" => $this->selected_user['id'],
                                "mode" => "add_group",
                            )
                        ),
                        "delete_target" => $this->model->get_link_url(
                            "userUpdate",
                            array(
                                "uid" => $this->selected_user['id'],
                                "mode" => "rm_group",
                                "did" => ":did",
                            )
                        ),
                        "label" => "Add Group",
                )))
            ))
        );
        $this->add_local_component("user_acl",
            new BaseStyleComponent("card", array(
                "is_expanded" => false,
                "is_collapsible" => true,
                "title" => "ACL",
                "children" => array(new BaseStyleComponent("acl", array(
                    "title" => "Page",
                    "items" => $this->model->get_acl_selected_user()
                )))
            ))
        );
    }

    /* Private Methods ********************************************************/

    private function output_button()
    {
        if($this->model->can_create_new_user())
            $this->output_local_component("new_user");
    }

    private function output_user_manipulation()
    {
        $this->output_local_component("user_groups");
        if($this->model->can_modify_user())
        {
            if(!$this->selected_user["blocked"])
                $this->output_local_component("user_blocking");
            else
                $this->output_local_component("user_unblocking");
        }
        if($this->model->can_delete_user())
            $this->output_local_component("user_delete");
    }

    private function output_users()
    {
        $this->output_local_component("users");
    }

    private function output_main_content()
    {
        if($this->selected_user != null)
        {
            $state = $this->selected_user['active'] ? "avctive" : "inactive";
            if($this->selected_user['blocked']) $state = "blocked";
            require __DIR__ . "/tpl_user.php";
        }
        else
        {
            require __DIR__ . "/tpl_users.php";
        }
    }

    private function output_user_groups()
    {
    }

    private function output_user_acl()
    {
        $this->output_local_component("user_acl");
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
