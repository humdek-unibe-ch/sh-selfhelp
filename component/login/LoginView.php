<?php
require_once __DIR__ . "/../IView.php";

/**
 * The view class of the login component.
 */
class LoginView implements IView
{
    /* Private Properties *****************************************************/

    private $router;
    private $model;
    private $controller;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $model
     *  The model instance of the login component.
     * @param object $controller
     *  The controller instance of the login component.
     */
    public function __construct($router, $model, $controller)
    {
        $this->router = $router;
        $this->model = $model;
        $this->controller = $controller;
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
     * Render the login view.
     */
    public function output_content()
    {
        $user_label = $this->model->get_db_field('user_label');
        $pw_label = $this->model->get_db_field('pw_label');
        $login_label = $this->model->get_db_field('login_button_label');
        $reset_label = $this->model->get_db_field('reset_button_label');
        $login_title = $this->model->get_db_field('login_title');
        $intro_title = $this->model->get_db_field('intro_title');
        $intro_content = $this->model->get_db_field('intro_content');
        require __DIR__ . "/tpl_login.php";
    }
}
?>
