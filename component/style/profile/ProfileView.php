<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the user profile component.
 */
class ProfileView extends BaseView
{
    /* Private Properties******************************************************/

    private $alert_fail;
    private $alert_success;

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
        $this->add_alert_component("danger", "alert_pw_fail",
            $this->model->get_db_field('alert_pw_fail'));
        $this->add_alert_component("success", "alert_pw_success",
            $this->model->get_db_field('alert_pw_success'));
        $this->add_alert_component("danger", "alert_del_fail",
            $this->model->get_db_field('alert_del_fail'));
        $this->add_alert_component("success", "alert_del_success",
            $this->model->get_db_field('alert_del_success'));
    }

    /* Private Methods ********************************************************/

    /**
     * Add an alert component to the local component list.
     *
     * @param string $type
     *  The alert style type.
     * @param string $name
     *  The alert component name.
     * @param string $content
     *  The alert content string.
     */
    private function add_alert_component($type, $name, $content)
    {
        $alert_content = new BaseStyleComponent("plaintext",
            array("text" => $content));
        $this->add_local_component($name,
            new BaseStyleComponent("alert", array(
                "children" => array($alert_content),
                "type" => $type
            ), true)
        );
    }

    /**
     * Renders an alert message on er delete fail or success.
     */
    private function output_alert_delete()
    {
        if($this->controller == null || $this->controller->has_delete_failed())
            $this->output_local_component("alert_del_fail");
        if($this->controller == null ||
                $this->controller->has_delete_succeeded())
            $this->output_local_component("alert_del_success");
    }

    /**
     * Renders an alert message on password change fail or success.
     */
    private function output_alert_pw_change()
    {
        if($this->controller == null ||
                $this->controller->has_pw_change_failed())
            $this->output_local_component("alert_pw_fail");
        if($this->controller == null ||
                $this->controller->has_pw_change_succeeded())
            $this->output_local_component("alert_pw_success");
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
        $local = array(__DIR__ . "/profile.css");
        return parent::get_css_includes($local);
    }

    /**
     * Render the user view.
     */
    public function output_content()
    {
        $url = $this->model->get_link_url('profile');
        $pw_title = $this->model->get_db_field('pw_change_title');
        $pw_label = $this->model->get_db_field('pw_label');
        $pw_confirm_label = $this->model->get_db_field('pw_confirm_label');
        $pw_change_label = $this->model->get_db_field('pw_change_action_label');
        $email_label = $this->model->get_db_field('user_label');
        $delete_title = $this->model->get_db_field('delete_title');
        $delete_label = $this->model->get_db_field('delete_label');
        $delete_content = $this->model->get_db_field('delete_content');
        $delete_confirm_label = $this->model->get_db_field('delete_confirm_label');
        $delete_cancel_label = $this->model->get_db_field('delete_cancel_label');
        $delete_confirm_content = $this->model->get_db_field('delete_confirm_content');
        require __DIR__ . "/tpl_profile.php";
    }
}
?>
