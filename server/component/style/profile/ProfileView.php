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
     * DB field 'alert_fail' (empty string).
     * The alert string when the password change fails.
     */
    private $alert_fail;

    /**
     * DB field 'alert_success' (empty string).
     * The alert string when the password change succeeds.
     */
    private $alert_success;

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
        $this->alert_fail = $this->model->get_db_field('alert_fail');
        $this->alert_success = $this->model->get_db_field('alert_success');
        $this->alert_del_fail = $this->model->get_db_field('alert_del_fail');
        $this->alert_del_success = $this->model->get_db_field('alert_del_success');
    }

    /* Private Methods ********************************************************/

    /**
     * Render an alert component.
     *
     * @param string $type
     *  The alert style type.
     * @param string $content
     *  The alert content string.
     */
    private function output_alert($type, $content)
    {
        $alert = new BaseStyleComponent("alert", array(
            "children" => array(
                new BaseStyleComponent("markdownInline",
                    array("text_md_inline" => $content))
            ),
            "type" => $type
        ));
        $alert->output_content();
    }

    /**
     * Renders an alert message on er delete fail or success.
     */
    private function output_alert_delete()
    {
        if($this->controller == null || $this->controller->has_delete_failed())
            $this->output_alert("danger", $this->alert_del_fail);
        if($this->controller == null
                || $this->controller->has_delete_succeeded())
            $this->output_alert("success", $this->alert_del_success);
    }

    /**
     * Renders an alert message on password change fail or success.
     */
    private function output_alert_change()
    {
        if($this->controller == null || $this->controller->has_change_failed())
            $this->output_alert("danger", $this->alert_fail);
        if($this->controller == null
                || $this->controller->has_change_succeeded())
            $this->output_alert("success", $this->alert_success);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the user view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_profile.php";
    }
}
?>
