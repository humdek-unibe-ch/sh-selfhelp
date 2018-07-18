<?php
require_once __DIR__ . "/../component/login/LoginComponent.php";

/**
 * Class to handle the login page. It is mainly a page wrapper for the login
 * component.
 */
class LoginPage extends BasePage
{
    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and creates an instance of a login component. The login component
     * handles the login process.
     *
     * @param object $router
     *  The router instance is used to generate valid links.
     */
    public function __construct($router)
    {
        parent::__construct($router, "login");

        $this->add_component("login",
            new LoginComponent($router, $this->db, $this->login));
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders the login
     * view and if necessary a login errorr message.
     */
    protected function output_content()
    {
        $this->output_component("login");
    }


    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
