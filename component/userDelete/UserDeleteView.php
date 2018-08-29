<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the user delete component.
 */
class UserDeleteView extends BaseView
{
    /* Private Properties *****************************************************/

    private $selected_user;

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
        $this->selected_user = $this->model->get_selected_user();
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to delete the user.",
                )))
            ))
        );
        $this->add_local_component("form",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Delete User",
                "type" => "danger",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the email address of the user.",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("form", array(
                        "label" => "Delete User",
                        "url" => $this->model->get_link_url("userDelete",
                            array("uid" => $this->selected_user['id'])),
                        "type" => "danger",
                        "cancel" => true,
                        "cancel_url" => $this->model->get_link_url("userSelect",
                            array("uid" => $this->selected_user['id'])),
                        "children" => array(
                            new BaseStyleComponent("input", array(
                                "type" => "email",
                                "name" => "email",
                                "is_required" => true,
                                "css" => "mb-3",
                                "placeholder" => "Enter Email Address",
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
            $url = $this->model->get_link_url("userSelect");
            require __DIR__ . "/tpl_success.php";
        }
        else
            require __DIR__ . "/tpl_user_delete.php";
    }
}
?>
