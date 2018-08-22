<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the user component.
 */
class UserView extends BaseView
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
        $selected_user = $this->model->get_selected_user();
        if($selected_user != null)
        {
            $acl_children = array();
            foreach($this->model->get_acl_selected_user() as $user => $acl)
                $acl_children[] = new BaseStyleComponent("template", array(
                    "path" => __DIR__ . "/tpl_acl_item.php",
                    "items" => array(
                        "user" => $user,
                        "select" => ($acl[0]) ? "checked" : "",
                        "insert" => ($acl[1]) ? "checked" : "",
                        "update" => ($acl[2]) ? "checked" : "",
                        "delete" => ($acl[3]) ? "checked" : ""
                    )
                ));
            $this->add_local_component("user",
                new BaseStyleComponent("card", array(
                    "is_expanded" => true,
                    "is_collapsible" => false,
                    "title" => "Access Rights of User <code>"
                        . $selected_user['email'] . "</code>",
                    "children" => array(
                        new BaseStyleComponent("template", array(
                            "path" => __DIR__ . "/tpl_acl.php",
                            "children" => $acl_children
                        ))
                    )
                ))
            );
        }
    }

    /* Private Methods ********************************************************/

    private function output_users()
    {
        $this->output_local_component("users");
    }

    private function output_user()
    {
        $this->output_local_component("user");
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
        $local = array(__DIR__ . "/user.css");
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
        $local = array(__DIR__ . "/user.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_user.php";
    }
}
?>
