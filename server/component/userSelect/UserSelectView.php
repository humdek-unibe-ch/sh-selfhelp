<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the user component.
 */
class UserSelectView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * An array of user properties (see UserModel::fetch_user).
     */
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
    }

    /* Private Methods ********************************************************/

    /**
     * Render the button to create a new user.
     */
    private function output_button()
    {
        if($this->selected_user !== null)
        {
            $button_table = new BaseStyleComponent("button", array(
                "label" => "Users",
                "url" => $this->model->get_link_url("userSelect"),
                "type" => "primary",
                "css" => "d-block mb-3",
            ));
            $button_table->output_content();
        }
        if($this->model->can_create_new_user())
        {
            $button_new = new BaseStyleComponent("button", array(
                "label" => "Create New User",
                "url" => $this->model->get_link_url("userInsert"),
                "type" => "secondary",
                "css" => "d-block mb-3",
            ));
            $button_new->output_content();
            $button_codes = new BaseStyleComponent("button", array(
                "label" => "Generate Validation Codes",
                "url" => $this->model->get_link_url("userGenCode"),
                "type" => "secondary",
                "css" => "d-block mb-3",
            ));
            $button_codes->output_content();
        }
    }

    /**
     * Render the card to block a user.
     */
    private function output_user_blocking()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
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
        ));
        $card->output_content();
    }

    /**
     * Render the card to delete a button.
     */
    private function output_user_delete()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
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
        ));
        $card->output_content();
    }

    /**
     * Render the card to manipulate user groups.
     */
    private function output_user_groups()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "User Groups",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "When assigned to a user, groups provide the user with a predefined set of access rights.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("sortableList", array(
                    "is_editable" => $this->model->can_modify_user(),
                    "items" => $this->model->get_selected_user_groups(),
                    "url_add" => $this->model->get_link_url(
                        "userUpdate",
                        array(
                            "uid" => $this->selected_user['id'],
                            "mode" => "add_group",
                        )
                    ),
                    "url_delete" => $this->model->get_link_url(
                        "userUpdate",
                        array(
                            "uid" => $this->selected_user['id'],
                            "mode" => "rm_group",
                            "did" => ":did",
                        )
                    ),
                    "label_add" => "Add Group",
            )))
        ));
        $card->output_content();
    }


    /**
     * Render the cards to manipulate a user, i.e. block, unblock. add/remove
     * groups, or delete a user.
     */
    private function output_user_manipulation()
    {
        $this->output_user_groups();
        if($this->model->can_modify_user())
        {
            if(!$this->selected_user["blocked"])
                $this->output_user_blocking();
            else
                $this->output_user_unblocking();
        }
        if($this->model->can_delete_user())
            $this->output_user_delete();
    }

    /**
     * Render the card to unblock the user.
     */
    private function output_user_unblocking()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
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
        ));
        $card->output_content();
    }

    /**
     * Render the list of users.
     */
    private function output_users()
    {
        $users = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Registered Users",
            "children" => array(new BaseStyleComponent("nestedList", array(
                "items" => $this->model->get_users(),
                "id_prefix" => "users",
                "is_collapsible" => false,
                "id_active" => $this->selected_user['id'],
            )))
        ));
        $users->output_content();
    }

    /**
     * Render the list of users and their activity.
     */
    private function output_user_activity()
    {
        require __DIR__ . "/tpl_user_activity.php";
    }

    /**
     * Render the activity table content.
     */
    private function output_user_activity_rows()
    {
        foreach($this->model->get_users() as $user)
        {
            $id = $user['id'];
            $email = $user['title'];
            /* $name = $user['name']; */
            $state = $user['state'];
            $row_state = "";
            if($state === "blocked")
                $row_state = "table-warning";
            if($state === "inactive")
                $row_state = "text-muted";
            $url = $user['url'];
            $last_login = $user['last_login'];
            $activity = $this->model->get_user_activity($id);
            $code = $this->model->get_user_code($id) ?? "undefined";
            require __DIR__ . "/tpl_user_activity_row.php";
        }
    }

    /**
     * Render the user description or the intro text.
     */
    private function output_main_content()
    {
        if($this->selected_user != null)
        {
            $state = $this->selected_user['active'] ? "active" : "inactive";
            if($this->selected_user['blocked']) $state = "blocked";
            require __DIR__ . "/tpl_user.php";
        }
        else
        {
            require __DIR__ . "/tpl_users.php";
        }
    }

    /**
     * Render the ACL list.
     */
    private function output_user_acl()
    {
        $acl = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => false,
            "is_collapsible" => true,
            "title" => "ACL",
            "children" => array(new BaseStyleComponent("acl", array(
                "title" => "Page",
                "items" => $this->model->get_acl_selected_user()
            )))
        ));
        $acl->output_content();
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
        $local = array(__DIR__ . "/css/users.css");
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
        $local = array(__DIR__ . "/js/users.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_main.php";
    }
}
?>
