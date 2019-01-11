<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the login component.
 * The login style component renders the login form. This is not made available
 * in the CMS and is only used internally.
 */
class RegisterView extends StyleView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'alert_fail' (empty string).
     * The alert string when the registration fails.
     */
    private $alert_fail;

    /**
     * DB field 'alert_success' (empty string).
     * The message to be displayed when the registration succeeds.
     */
    private $alert_success;

    /**
     * DB field 'label_user' (empty string).
     * The placeholder of the user-name field.
     */
    private $user_label;

    /**
     * DB field 'label_pw' (empty string).
     * The placeholder of the code field.
     */
    private $code_label;

    /**
     * DB field 'label_submit' (empty string).
     * The label of the submit button.
     */
    private $submit_label;

    /**
     * DB field 'success' (empty string)
     * The title of the success jumbotron.
     */
    private $success;

    /**
     * DB field 'title' (empty string).
     * The title of the card with the login form fields.
     */
    private $title;

    /**
     * DB field 'type' ('success').
     * The style of the card and the submit button. E.g. 'warning', 'danger', etc.
     */
    private $type;


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     * @param object $controller
     *  The controller instance of the login component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->alert_fail = $this->model->get_db_field('alert_fail');
        $this->alert_success = $this->model->get_db_field('alert_success');
        $this->user_label = $this->model->get_db_field('label_user');
        $this->code_label = $this->model->get_db_field('label_pw');
        $this->submit_label = $this->model->get_db_field('label_submit');
        $this->title = $this->model->get_db_field('title');
        $this->success = $this->model->get_db_field("success");
        $this->type = $this->model->get_db_field("type", "success");

        $this->add_local_component("alert", new BaseStyleComponent("alert",
            array(
                "children" => array(new BaseStyleComponent("plaintext", array(
                        "text" => $this->alert_fail))),
                "type" => "danger"
            )
        ));
    }

    /* Private Methods ********************************************************/

    /**
     * Renders an alert message if the login failed.
     */
    private function output_alert()
    {
        if($this->controller == null || $this->controller->has_failed())
            $this->output_local_component("alert");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        if($this->controller == null || !$this->controller->has_succeeded())
        {
            require __DIR__ . "/tpl_register.php";
        }
        if($this->controller == null || $this->controller->has_succeeded())
        {
            require __DIR__ . "/tpl_success.php";
        }
    }
}
?>
