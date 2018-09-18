<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the group delete component.
 */
class GroupDeleteView extends BaseView
{
    /* Private Properties *****************************************************/

    private $selected_group;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user delete component.
     * @param object $controller
     *  The controller instance of the user delete component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->selected_group = $this->model->get_selected_group();
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to delete the group.",
                )))
            ))
        );
        $this->add_local_component("form",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Delete Group",
                "type" => "danger",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the name of the group.",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("form", array(
                        "label" => "Delete Group",
                        "url" => $this->model->get_link_url("groupDelete",
                            array("gid" => $this->selected_group['id'])),
                        "type" => "danger",
                        "url_cancel" => $this->model->get_link_url("groupSelect",
                            array("gid" => $this->selected_group['id'])),
                        "children" => array(
                            new BaseStyleComponent("input", array(
                                "type-input" => "text",
                                "name" => "name",
                                "is_required" => true,
                                "css" => "mb-3",
                                "placeholder" => "Enter Group Name",
                                "is_user_input" => false,
                            )),
                        )
                    )),
                )
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
     * Rednet the delete form.
     */
    private function output_form()
    {
        $this->output_local_component("form");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $url = $this->model->get_link_url("groupSelect");
            require __DIR__ . "/tpl_success.php";
        }
        else
            require __DIR__ . "/tpl_group_delete.php";
    }
}
?>
