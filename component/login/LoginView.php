<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the login component.
 */
class LoginView extends BaseView
{
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
    }

    /* Private Methods ********************************************************/

    /**
     * Renders an alert message if the login failed.
     */
    private function output_alert()
    {
        $alert = $this->model->get_db_field('alert');
        if($this->controller->has_login_failed())
            require __DIR__ . "/tpl_alert.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes()
    {
        return array(
            __DIR__ . "/login.css"
        );
    }

    /**
     * Render the login view.
     */
    public function output_content()
    {
        $url = $this->model->get_link_url('login');
        $user_label = $this->model->get_db_field('user_label');
        $pw_label = $this->model->get_db_field('pw_label');
        $login_label = $this->model->get_db_field('login_label');
        $reset_label = $this->model->get_db_field('reset_pw_label');
        $login_title = $this->model->get_db_field('login_title');
        $intro_title = $this->model->get_db_field('intro_title');
        $intro_content = $this->model->get_db_field('intro_text');
        require __DIR__ . "/tpl_login.php";
    }
}
?>
