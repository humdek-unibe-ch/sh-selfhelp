<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the user profile component.
 */
class ValidateView extends StyleView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'title' (empty string)
     * The title of the page.
     */
    private $title;

    /**
     * DB field 'subtitle' (empty string)
     * The subtitle of the page.
     */
    private $subtitle;

    /**
     * DB field 'label_name' (empty string)
     * The label of the name input field.
     */
    private $name_label;

    /**
     * DB field 'name_placeholder' (empty string)
     * The placeholder text in the name input field.
     */
    private $name_placeholder;

    /**
     * DB field 'name_description' (empty string)
     * The text displayued below the name input field in small letters.
     */
    private $name_descrtiption;

    /**
     * DB field 'label_pw' (empty string)
     * The label of the password field.
     */
    private $pw_label;

    /**
     * DB field 'pw_placeholder' (empty string)
     * The placeholder text in the password input field.
     */
    private $pw_placeholder;

    /**
     * DB field 'lablel_pw_confirm' (empty string)
     * The label of the password confirmation input field.
     */
    private $pw_confirm_label;

    /**
     * DB field 'label_gender' (empty string)
     * The label of the gender selection fields.
     */
    private $gender_label;

    /**
     * DB field 'gender_male' (empty string)
     * The male gender text.
     */
    private $gender_male;

    /**
     * DB field 'gender_female' (empty string)
     * The female gender string.
     */
    private $gender_female;

    /**
     * DB field 'label_activate' (empty string)
     * The label of the submit button.
     */
    private $activate_label;

    /**
     * DB field 'alert_fail' (empty string)
     * The alert message on failure.
     */
    private $alert_fail;

    /**
     * DB field 'alert_success' (empty string)
     * The success message placed in the success jumbotron.
     */
    private $alert_success;

    /**
     * DB field 'success' (empty string)
     * The title of the success jumbotron.
     */
    private $success;

    /**
     * DB field 'label_login' (empty string)
     * The label of the button linking to the login.
     */
    private $login_action_label;

    /**
     * DB field 'name' (empty string)
     * The form name under which the custom input fields will be grouped.
     */
    private $custom_form_name;

    /**
     * The controller instance of the formUserInput component.
     */
    private $ui_controller;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the validate component.
     * @param object $controller
     *  The controller instance of the validate component.
     * @param object $ui_controller
     *  The controller instance of the formUserInput component.
     */
    public function __construct($model, $controller, $ui_controller)
    {
        parent::__construct($model, $controller);
        $this->ui_controller = $ui_controller;
        $this->title = $this->model->get_db_field("title");
        $this->subtitle = $this->model->get_db_field("subtitle");
        $this->name_label = $this->model->get_db_field("label_name");
        $this->name_placeholder = $this->model->get_db_field("name_placeholder");
        $this->name_description = $this->model->get_db_field("name_description");
        $this->pw_label = $this->model->get_db_field('label_pw');
        $this->pw_placeholder = $this->model->get_db_field('pw_placeholder');
        $this->pw_confirm_label = $this->model->get_db_field('label_pw_confirm');
        $this->gender_label = $this->model->get_db_field("label_gender");
        $this->gender_male = $this->model->get_db_field("gender_male");
        $this->gender_female = $this->model->get_db_field("gender_female");
        $this->activate_label = $this->model->get_db_field("label_activate");
        $this->alert_fail = $this->model->get_db_field("alert_fail");
        $this->alert_success = $this->model->get_db_field("alert_success");
        $this->success = $this->model->get_db_field("success");
        $this->login_action_label = $this->model->get_db_field("label_login");
        $this->custom_form_name = $this->model->get_db_field("name");
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => $this->alert_fail,
                )))
            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        if($this->controller == null || $this->controller->has_failed())
            $this->output_local_component("alert-fail");
        if($this->ui_controller !== null && $this->ui_controller->has_failed())
        {
            foreach($this->ui_controller->get_error_msgs() as $msg)
            {
                $alert = new BaseStyleComponent("alert", array(
                    "type" => "danger",
                    "is_dismissable" => true,
                    "children" => array(new BaseStyleComponent("plaintext", array(
                        "text" => $msg,
                    )))
                ));
                $alert->output_content();
            }
        }
    }

    /**
     * Render to user-defined input fields.
     */
    private function output_custom_fields()
    {
        if(count($this->children) === 0) return;
        $input = new BaseStyleComponent('input', array(
            'type_input' => 'hidden',
            'name' => '__form_name',
            'value' => $this->custom_form_name,
        ));
        $input->output_content();
        foreach($this->children as $child)
        {
            $input = $child->get_style_instance();
            if(is_a($input, "FormFieldComponent"))
                $input->enable_user_input();
            $child->output_content();
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the user view.
     */
    public function output_content()
    {
        if($this->controller == null || !$this->controller->has_succeeded()
            || $this->ui_controller->has_failed())
        {
            $gender = $this->model->get_user_gender();
            $male_checked = ($gender === "male") ? "checked" : "";
            $female_checked = ($gender === "female") ? "checked" : "";
            $name = $this->model->get_user_name();
            require __DIR__ . "/tpl_validate.php";
        }
        if($this->model->is_cms_page()
            || ($this->controller !== null && $this->controller->has_succeeded()
                && !$this->ui_controller->has_failed()))
        {
            $url = $this->model->get_link_url("login");
            require __DIR__ . "/tpl_success.php";
        }
    }
}
?>
