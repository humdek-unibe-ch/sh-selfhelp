<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the group insert component.
 */
class GroupInsertView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user insert component.
     * @param object $controller
     *  The controller instance of the user insert component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to create a new group.",
                )))
            ))
        );
        $this->add_local_component("group-acl",
            new BaseStyleComponent("acl", array(
                "title" => "Function",
                "is_editable" => true,
                "items" => $this->model->get_simple_acl_selected_group()
            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        if($this->controller->has_failed())
            $this->output_local_component("alert-fail");
    }

    /**
     * Render the ACL form.
     */
    private function output_group_acl()
    {
        $this->output_local_component("group-acl");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $group = $this->controller->get_new_name();
            $url = $this->model->get_link_url("groupSelect",
                array("gid" => $this->controller->get_new_gid()));
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $action_url = $this->model->get_link_url("groupInsert");
            $cancel_url = $this->model->get_link_url("groupSelect");
            require __DIR__ . "/tpl_insert_group.php";
        }
    }
}
?>
