<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the user profile component.
 */
class ValidateView extends BaseView
{
    /* Private Properties******************************************************/

    private $title;
    private $subtitle;
    private $name_label;
    private $name_placeholder;
    private $name_descrtiption;
    private $pw_label;
    private $pw_placeholder;
    private $pw_confirm_label;
    private $gender_label;
    private $gender_male;
    private $gender_female;
    private $gender_description;
    private $activate_label;
    private $alert_fail;
    private $alert_success;
    private $success;
    private $login_action_label;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the user profile component.
     * @param object $controller
     *  The controller instance of the user profile component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
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
    }

    /* Public Methods *********************************************************/

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(__DIR__ . "/validate.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the user view.
     */
    public function output_content()
    {
        if($this->controller == null || !$this->controller->has_succeeded())
            require __DIR__ . "/tpl_validate.php";
        if($this->controller == null || $this->controller->has_succeeded())
        {
            $url = $this->model->get_link_url("login");
            require __DIR__ . "/tpl_success.php";
        }
    }
}
?>
