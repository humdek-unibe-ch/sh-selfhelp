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
     * DB field 'open_registration' (false).
     * If set to true, users can register without a vlaidation code. 
     * Upon registration the code will be automatically generated
     */
    private $open_registration;

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

    /**
     * If enabled the registration will be based on the logic for anonymous_users
     */
    private $anonymous_users = false;

    /**
     * Label for security question 1
     */
    private $label_sec_q_1;

    /**
     * Label for security question 2
     */
    private $label_sec_q_2;

    /**
     * The description of the anonymous user registration process
     */
    private $anonymous_users_registration;


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
        $this->open_registration = $this->model->get_db_field("open_registration",false);
        $this->anonymous_users = $this->model->is_anonymous_users();
        $this->label_sec_q_1 = $this->model->get_db_field('label_security_question_1');
        $this->label_sec_q_2 = $this->model->get_db_field('label_security_question_2');
        $this->anonymous_users_registration = $this->model->get_db_field('anonymous_users_registration');

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
        if ($this->controller == null || !$this->controller->has_succeeded()) {
            if ($this->anonymous_users) {
                require __DIR__ . "/tpl_anonymous_users_register.php";
            } else {
                require __DIR__ . "/tpl_register.php";
            }
        }
        if ($this->controller == null || $this->controller->has_succeeded()) {
            require __DIR__ . "/tpl_success.php";
        }
    }

    /**
     * Output security questions for anonymous registration
     */
    public function output_security_questions()
    {
        $md_anonymous_users_registration =  new BaseStyleComponent("markdown", array(
            "text_md" => $this->anonymous_users_registration,
        ));
        $md_anonymous_users_registration->output_content();
        $sec_q_1 = new BaseStyleComponent("select", array(
            "label" => $this->label_sec_q_1,
            "id" => "security_question_1",
            "name" => "security_question_1",
            "css" => '',
            "live_search" => false,
            "is_multiple" => false,
            "is_required" => true,
            "items" => $this->model->get_security_questions(),
        ));
        $sec_q_1->output_content();
        $sec_q_1_answer = new BaseStyleComponent("input", array(
            "name" => "security_question_1_answer",
            "is_required" => true,
            "css" => "mb-3"
        ));
        $sec_q_1_answer->output_content();
        $sec_q_2 = new BaseStyleComponent("select", array(
            "label" => $this->label_sec_q_2,
            "id" => "security_question_2",
            "name" => "security_question_2",
            "css" => '',
            "live_search" => false,
            "is_multiple" => false,
            "is_required" => true,
            "items" => $this->model->get_security_questions(),
        ));
        $sec_q_2->output_content();
        $sec_q_2_answer = new BaseStyleComponent("input", array(
            "name" => "security_question_2_answer",
            "is_required" => true,
            "css" => "mb-3"
        ));
        $sec_q_2_answer->output_content();
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
        $style['security_questions'] = $this->model->get_security_questions();
        return $style;
    }
	
}
?>
