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
     * @param object $controller
     *  The controller instance of the user component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->add_local_component("new_user", new BaseStyleComponent("button",
            array(
                "label" => "Create New User",
                "url" => $this->model->get_link_url("user_insert"),
                "type" => "secondary"
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
        $this->selected_user = $this->model->get_selected_user();
        $this->add_local_component("user_groups",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => true,
                "title" => "Groups of User <code>"
                    . $this->selected_user['email'] . "</code>",
                "children" => array(new BaseStyleComponent("sortableList", array(
                    "edit" => true,
                    "items" => $this->model->get_selected_user_groups(),
                    "insert_target" => "bla",
                    "delete_target" => "bla",
                    "label" => "Add Group",
                )))
            ))
        );
        $this->add_local_component("user_acl",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => true,
                "title" => "Access Rights of User <code>"
                    . $this->selected_user['email'] . "</code>",
                "children" => array(new BaseStyleComponent("acl", array(
                    "title" => "User",
                    "items" => $this->model->get_acl_selected_user()
                )))
            ))
        );
    }

    /* Private Methods ********************************************************/

    private function output_button()
    {
        $this->output_local_component("new_user");
    }

    private function output_users()
    {
        $this->output_local_component("users");
    }

    private function output_user_groups()
    {
        $this->output_local_component("user_groups");
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
        if($this->selected_user != null)
            require __DIR__ . "/tpl_user.php";
        else
        {
            require __DIR__ . "/tpl_users.php";
        }
    }
}
?>
