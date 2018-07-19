<?php
require_once __DIR__ . "/SectionPage.php";

/**
 * The class to handle the home page. It extends the SectionPage and will render
 * all sections that are attributed to the home page in the DB.
 */
class HomePage extends SectionPage
{
    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and checks the login fields if they are set. If the fields are set
     * but the login fails, the page is redirected to the login page.
     *
     * @param object $router
     *  The router instance is used to generate valid links.
     * @param string $keyword
     *  The identification name of the page.
     */
    public function __construct($router)
    {
        parent::__construct($router, "home");
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
