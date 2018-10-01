<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the ResetPasswordComponent.
 */
class ResetPasswordView extends BaseView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'alert_fail' (empty string).
     * The alert string when the reset fails.
     */
    private $alert_fail;

    /**
     * DB field 'alert_success' (empty string).
     * The text to be displayed in the success jumbotron.
     */
    private $alert_success;

    /**
     * DB field 'label_login' (empty string).
     * The label of the login link.
     */
    private $login_label;

    /**
     * DB field 'placeholder' (empty string).
     * The placeholder text inside the email input form.
     */
    private $placeholder;

    /**
     * DB field 'label_pw_reset' (empty string).
     * The label of the password reset link.
     */
    private $reset_label;

    /**
     * DB field 'success' (empty string).
     * The success title.
     */
    private $success;

    /**
     * DB field 'text_md' (empty string).
     * The text to be placed in the jumbotron.
     */
    private $text;


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
        $this->login_label = $this->model->get_db_field('label_login');
        $this->reset_label = $this->model->get_db_field('label_pw_reset');
        $this->success = $this->model->get_db_field('success');
        $this->text = $this->model->get_db_field('text_md');
        $this->placeholder = $this->model->get_db_field('placeholder');

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
            require __DIR__ . "/tpl_reset.php";
        if($this->controller == null || $this->controller->has_succeeded())
        {
            $url = $this->model->get_link_url('login');
            require __DIR__ . "/tpl_success.php";
        }
    }
}
?>
