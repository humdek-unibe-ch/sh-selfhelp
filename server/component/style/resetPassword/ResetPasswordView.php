<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";
require_once __DIR__ . "/../emailFormBase/EmailFormBaseView.php";

/**
 * The view class of the ResetPasswordComponent.
 * This style is not available for selection in the CMS.
 */
class ResetPasswordView extends EmailFormBaseView
{
    /* Private Properties******************************************************/

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
        $this->text = $this->model->get_db_field('text_md');
        $this->label = $this->model->get_db_field('label_pw_reset');
        $this->placeholder = $this->model->get_db_field('placeholder');
    }

    /* Private Methods ********************************************************/

    /**
     * Render the email form.
     */
    private function output_form()
    {
        parent::output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        if ($this->model->is_anonymous_users() && $this->model->is_reset_password_enabled()) {
            require __DIR__ . "/tpl_reset_anonymous_user.php";
        } else {
            require __DIR__ . "/tpl_reset.php";
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
        $style['anonymous_users'] = intval($this->model->is_anonymous_users());
        $style['is_reset_password_enabled'] = intval($this->model->is_reset_password_enabled());
        $user_security_questions = $this->model->get_user_security_questions();
        $security_questions = $this->model->get_security_questions();
        $security_questions_labels = array();
        if ($user_security_questions) {
            $user_security_questions = json_decode($user_security_questions['security_questions']);
            foreach ($user_security_questions as $key => $value) {
                $security_questions_labels[] = array(
                    "id" => $key,
                    "text" => $security_questions[$key]
                );
            }
        }
        $style['security_questions_labels'] = $security_questions_labels;
        if(count($security_questions_labels) > 0){
            $style['reset_user_name'] =  $this->model->get_reset_user_name();
        }        
        return $style;
    }


    /**
     * Output reset form for anonymous users
     */
    public function output_reset_anonymous_user()
    {
        $reset_user_name = $this->model->get_reset_user_name();
        if ($reset_user_name) {
            $user_security_questions = $this->model->get_user_security_questions();
            if ($user_security_questions) {
                $user_security_questions = json_decode($user_security_questions['security_questions']);
                $security_questions = $this->model->get_security_questions();
                $children = array(new BaseStyleComponent("input", array(
                    "type_input" => "hidden",
                    "name" => "reset_anonymous_user",
                    "value" => true,
                )), new BaseStyleComponent("input", array(
                    "type_input" => "hidden",
                    "name" => "reset_anonymous_user_sec_q",
                    "value" => true,
                )), new BaseStyleComponent("input", array(
                    "type_input" => "hidden",
                    "name" => "user_name",
                    "value" => $reset_user_name,
                )));
                foreach ($user_security_questions as $key => $value) {
                    $children[] = new BaseStyleComponent("input", array(
                        "type_input" => "text",
                        "label" => $security_questions[$key],
                        "name" => $key,
                        "id" => "reset-user-name-" . $key,
                    ));
                }
                $resetForm = new BaseStyleComponent("form", array(
                    "label" => $this->label,
                    "type" => 'warning',
                    "id" => "reset-password",
                    "url" => $this->model->get_link_url('reset_password'),
                    "children" => $children
                ));
                $resetForm->output_content();
            }
        } else {
            $resetForm = new BaseStyleComponent("form", array(
                "label" => $this->label,
                "type" => 'warning',
                "id" => "reset-password",
                "url" => $this->model->get_link_url('reset_password'),
                "children" => array(
                    new BaseStyleComponent("input", array(
                        "type_input" => "hidden",
                        "name" => "reset_anonymous_user",
                        "value" => true,
                    )),
                    new BaseStyleComponent("input", array(
                        "type_input" => "text",
                        "label" => "User name",
                        "name" => "user_name",
                        "id" => "reset-user-name",
                        "placeholder" => $this->placeholder,
                    ))
                )
            ));
            $resetForm->output_content();
        }
    }
}
?>
