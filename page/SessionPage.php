<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/../component/sessions/session/SessionComponent.php";

/**
 * This class serves as a wrapper for a session component.
 */
class SessionPage extends BasePage
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
        parent::__construct($router, $db, "session");
        $this->add_component("component",
            new SessionComponent($this->router, $this->db, $id));
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        $this->output_component("component");
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
