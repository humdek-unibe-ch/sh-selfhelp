<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the login component.
 */
class LoginView extends BaseView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'alert_fail' (empty string).
     * The alert string when the login fails.
     */
    private $alert_fail;

    /**
     * DB field 'label_user' (empty string).
     * The placeholder of the user-name field.
     */
    private $user_label;

    /**
     * DB field 'label_pw' (empty string).
     * The placeholder of the password field.
     */
    private $pw_label;

    /**
     * DB field 'login_label' (empty string).
     * The label of the login button.
     */
    private $login_label;

    /**
     * DB field 'label_reset_pw' (empty string).
     * The label of the password reset link.
     */
    private $reset_label;

    /**
     * DB field 'login_title' (empty string).
     * The title of the card with the login form fields.
     */
    private $login_title;

    /**
     * DB field 'intro_title' (empty string).
     * The title of the introduction.
     */
    private $intro_title;

    /**
     * DB field 'intro_content' (empty string).
     * The content of the introduction.
     */
    private $intro_content;


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
        $this->user_label = $this->model->get_db_field('label_user');
        $this->pw_label = $this->model->get_db_field('label_pw');
        $this->login_label = $this->model->get_db_field('label_login');
        $this->reset_label = $this->model->get_db_field('label_reset_pw');
        $this->login_title = $this->model->get_db_field('login_title');
        $this->intro_title = $this->model->get_db_field('intro_title');
        $this->intro_content = $this->model->get_db_field('intro_text');

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
        if($this->controller == null || $this->controller->has_login_failed())
            $this->output_local_component("alert");
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
        $local = array(__DIR__ . "/login.css");
        return parent::get_css_includes($local);
    }

    /**
     * Render the login view.
     */
    public function output_content()
    {
        $url = $this->model->get_link_url('login');
        require __DIR__ . "/tpl_login.php";
    }
}
?>
