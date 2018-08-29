<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the user insert component.
 */
class UserInsertView extends BaseView
{
    /* Private Properties *****************************************************/

    private $selected_user;

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
        $this->selected_user = $this->model->get_selected_user();
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to create a new user.",
                )))
            ))
        );
        $this->add_local_component("select", new BaseStyleComponent("select",
            array(
                "name" => "user_groups[]",
                "is_multiple" => true,
                "items" => $this->model->get_group_options()
            )
        ));
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
     * Render the group selection from.
     */
    private function output_group_selection()
    {
        $this->output_local_component("select");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $user = $this->controller->get_new_email();
            $url = $this->model->get_link_url("userSelect",
                array("uid" => $this->controller->get_new_uid()));
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $action_url = $this->model->get_link_url("userInsert");
            $cancel_url = $this->model->get_link_url("userSelect");
            require __DIR__ . "/tpl_insert_user.php";
        }
    }
}
?>
