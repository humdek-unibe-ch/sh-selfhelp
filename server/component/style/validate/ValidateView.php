<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     * DB field 'gender_divers' (empty string)
     * The female gender string.
     */
    private $gender_divers;

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
     * DB field 'name_description' (empty string)
     * The name description
     */
    private $name_description;

    /**
     * DB field 'value_gender' (empty string)
     * The default value of the gender. If set it will be hidden
     */
    private $value_gender;

    /**
     * DB field 'value_name' (empty string)
     * The default value of the user name. If set it will be hidden
     */
    private $value_name;

    /**
     * The controller instance of the formUserInput component.
     */
    private $ui_controller;    

    /**
     * If enabled the registration will be based on the logic for anonymous_users
     */
    private $anonymous_users = false;

    /**
     * The description for the anonymous user name
     */
    private $anonymous_user_name_description;

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
        $this->gender_divers = $this->model->get_db_field("gender_divers", "divers");
        $this->activate_label = $this->model->get_db_field("label_activate");
        $this->alert_fail = $this->model->get_db_field("alert_fail");
        $this->alert_success = $this->model->get_db_field("alert_success");
        $this->success = $this->model->get_db_field("success");
        $this->login_action_label = $this->model->get_db_field("label_login");
        $this->custom_form_name = $this->model->get_db_field("name");
        $this->value_name = $this->model->get_db_field("value_name", "");
        $this->value_gender = $this->model->get_db_field("value_gender", "");
        $this->anonymous_users = $this->model->is_anonymous_users();
        $this->anonymous_user_name_description = $this->model->get_db_field("anonymous_user_name_description");
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
    private function check_custom_fields()
    {
        if (count($this->children) === 0) {
            require __DIR__ . "/tpl_cms_children_holder.php";
        } else {
            if (
                method_exists($this->model, "is_cms_page") && $this->model->is_cms_page() &&
                method_exists($this->model, "is_cms_page_editing") && $this->model->is_cms_page_editing() &&
                $this->model->get_services()->get_user_input()->is_new_ui_enabled()
            ) {
                require __DIR__ . "/tpl_custom_fields_edit.php";
            } else {
                $this->output_custom_fields();
            }            
        }        
    }

    /**
     * Output custom fields
     */
    private function output_custom_fields() {
        $input = new BaseStyleComponent('input', array(
            'type_input' => 'hidden',
            'name' => '__form_name',
            'value' => $this->custom_form_name,
        ));
        $input->output_content();
        $this->output_custom_fields_children($this->children);
        foreach ($this->children as $child) {
            $child->output_content();
        }
    }
    
    /**
     * Recursively load children fields and enable them if they are form fields
     * @param array $children
     * The children elements that we will loop
     */
    private function output_custom_fields_children($children){
        foreach ($children as $child) {
            $input = $child->get_style_instance();
            if (is_a($input, "FormFieldComponent")){
                $input->enable_user_input();                
            }
            $this->output_custom_fields_children($child->get_children());
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
            $divers_checked = ($gender === "divers") ? "checked" : "";
            if ($this->value_gender) {
                switch ($this->value_gender) {
                    case MALE_GENDER_ID:
                        $male_checked = "checked";
                        break;
                    case FEMALE_GENDER_ID:
                        $female_checked = "checked";
                        break;
                    case DIVERS_GENDER_ID:
                        $divers_checked = "checked";
                        break;
                }
            }
            $name = $this->model->get_user_name();
            if ($this->value_name) {
                $name = $this->value_name;
            }
            if ($this->anonymous_users) {
                require __DIR__ . "/tpl_validate_anonymous_user.php";
            } else {
                require __DIR__ . "/tpl_validate.php";
            }
        }
        if($this->model->is_cms_page()
            || ($this->controller !== null && $this->controller->has_succeeded()
                && !$this->ui_controller->has_failed()))
        {
            $url = $this->model->get_link_url("login");
            require __DIR__ . "/tpl_success.php";
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
        $style['user_name'] = $this->model->get_user_name();
        $style['css_gender'] = $this->get_css_gender();
        return $style;
    }

    /**
     * Get the css for the gender group. If there are default value the group is not displayed
     * @return string
     */
    public function get_css_gender(){
        return $this->value_gender ? "d-none": "";
    }

    /**
     * Get the css for the name group. If there are default value the group is not displayed
     * @return string
     */
    public function get_css_name(){
        return $this->value_name ? "d-none": "";
    }
}
?>
