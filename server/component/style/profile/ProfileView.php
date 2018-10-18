<?php
require_once __DIR__ . "/../StyleView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the user profile component.
 * This style component renders the profile page of a user. This is style is not
 * made available for selection in the CMS.
 */
class ProfileView extends StyleView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'alert_pw_fail' (empty string).
     * The alert string when the password change fails.
     */
    private $alert_pw_fail;

    /**
     * DB field 'alert_pw_success' (empty string).
     * The alert string when the password change succeeds.
     */
    private $alert_pw_success;

    /**
     * DB field 'alert_del_fail' (empty string).
     * The alert string when the user deletion fails.
     */
    private $alert_del_fail;

    /**
     * DB field 'alert_del_success' (empty string).
     * The alert string when the user deletion succeeds.
     */
    private $alert_del_success;

    /**
     * DB field 'pw_change_title' (empty string).
     * The title of the password change card.
     */
    private $pw_change_title;

    /**
     * DB field 'label_pw' (empty string).
     * The placeholder of the password input field.
     */
    private $pw_label;

    /**
     * DB field 'label_pw_confirm' (empty string).
     * The placeholder of the password confirmation input field.
     */
    private $pw_confirm_label;

    /**
     * DB field 'label_change' (empty string).
     * The label of the password change submit button.
     */
    private $pw_change_action_label;

    /**
     * DB field 'label_user' (empty string).
     * The label of the email confirmation input field.
     */
    private $user_label;

    /**
     * DB field 'delete_title' (empty string).
     * The title of the delete user card.
     */
    private $delete_title;

    /**
     * DB field 'label_delete' (empty string).
     * The lable of the delete user button.
     */
    private $delete_label;

    /**
     * DB field 'delete_content' (empty string).
     * The description of the delete card body.
     */
    private $delete_content;

    /**
     * DB field 'label_delete_confirm' (empty string).
     * The label of the delete user submit button.
     */
    private $delete_confirm_label;

    /**
     * DB field 'delete_confirm_content' (empty string).
     * The content of the delete user confirmation box.
     */
    private $delete_confirm_content;

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
        $this->alert_pw_fail = $this->model->get_db_field('alert_pw_fail');
        $this->alert_pw_success = $this->model->get_db_field('alert_pw_success');
        $this->alert_del_fail = $this->model->get_db_field('alert_del_fail');
        $this->alert_del_success = $this->model->get_db_field('alert_del_success');
        $this->pw_change_title = $this->model->get_db_field('pw_change_title');
        $this->pw_label = $this->model->get_db_field('label_pw');
        $this->pw_confirm_label = $this->model->get_db_field('label_pw_confirm');
        $this->pw_change_action_label = $this->model->get_db_field('label_change');
        $this->user_label = $this->model->get_db_field('label_user');
        $this->delete_title = $this->model->get_db_field('delete_title');
        $this->delete_label = $this->model->get_db_field('label_delete');
        $this->delete_content = $this->model->get_db_field('delete_content');
        $this->delete_confirm_label = $this->model->get_db_field('label_delete_confirm');
        $this->delete_confirm_content = $this->model->get_db_field('delete_confirm_content');

        $this->add_alert_component("danger", "alert_pw_fail",
            $this->alert_pw_fail);
        $this->add_alert_component("success", "alert_pw_success",
            $this->alert_pw_success);
        $this->add_alert_component("danger", "alert_del_fail",
            $this->alert_del_fail);
        $this->add_alert_component("success", "alert_del_success",
            $this->alert_del_success);
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
            ))
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
     * Render the user view.
     */
    public function output_content()
    {
        $url = $this->model->get_link_url('profile');
        require __DIR__ . "/tpl_profile.php";
    }
}
?>
