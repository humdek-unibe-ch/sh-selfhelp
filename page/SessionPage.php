<?php
require_once __DIR__ . "/NavigationPage.php";
require_once __DIR__ . "/../component/sessions/session/SessionComponent.php";

/**
 * This class serves as a wrapper for a session component.
 */
class SessionPage extends NavigationPage
{
    /* Private Properties *****************************************************/

    private $sections;

    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and collects all sections that are allocated to the current page.
     * For each section, a StyleComponent is created and added to the component
     * list of the page.
     *
     * @param object $router
     *  The router instance is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param string $id
     *  The id of the session to display.
     */
    public function __construct($router, $db, $id)
    {
        parent::__construct($router, $db, "session", $id);
        $this->add_navigation_component(new SessionComponent($this->router,
            $this->db, $id, $this->get_nav()));
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        parent::output_content();
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
