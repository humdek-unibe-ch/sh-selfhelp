<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the login component.
 * The login style component renders the login form. This is not made available
 * in the CMS and is only used internally.
 */
class LoginView extends StyleView
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
     * DB field 'label_pw_reset' (empty string).
     * The label of the password reset link.
     */
    private $reset_label;

    /**
     * DB field 'login_title' (empty string).
     * The title of the card with the login form fields.
     */
    private $login_title;

    /**
     * DB field 'type' ('success').
     * The style of the card and the submit button. E.g. 'warning', 'danger', etc.
     */
    private $type;

    /**
     * If enabled the registration will be based on the logic for anonymous_users
     */
    private $anonymous_users = false;



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
        $this->reset_label = $this->model->get_db_field('label_pw_reset');
        $this->login_title = $this->model->get_db_field('login_title');
        $this->type = $this->model->get_db_field("type", "dark");
        $this->anonymous_users = $this->model->is_anonymous_users();
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
     * Render the login view.
     */
    public function output_content()
    {
        $url = $this->model->get_link_url('login');
        $reset_url = $this->model->get_link_url('reset_password');
        if ($this->anonymous_users) {
            require __DIR__ . "/tpl_login_anonymous_user.php";
        } else {
            require __DIR__ . "/tpl_login.php";
        }
    }

    /**
     * Output the style for mobile
     * @return object 
     * Return te style
     */
    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        $style['anonymous_users'] = $this->anonymous_users;
        return $style;
    }

    /**
     * Render the reset password button.
     */
    public function output_reset_password()
    {
        $reset_url = $this->model->get_link_url('reset_password');
        if ($this->model->is_reset_password_enabled()) {
            require __DIR__ . "/tpl_reset_password.php";
        }
    }

}
?>
