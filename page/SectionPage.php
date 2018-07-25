<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/../component/style/StyleComponent.php";

/**
 * This class maps the section structure of the DB. A section page consists
 * solely of a collection of sections as defined in the database.
 */
class SectionPage extends BasePage
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
     * @param string $keyword
     *  The identification name of the page.
     */
    public function __construct($router, $db, $keyword)
    {
        parent::__construct($router, $db, $keyword);
        $sql = "SELECT ps.id_sections AS id
            FROM pages_sections AS ps
            WHERE ps.id_pages = :id_page
            ORDER BY ps.position";
        $this->sections = $this->db->query_db($sql,
            array(
                ":id_page" => $this->id_page
            )
        );
        foreach($this->sections as $section)
        {
            $this->add_component("section-" . $section['id'],
                new StyleComponent($this->router, $this->db, $section['id']));
        }
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        foreach($this->sections as $section)
        {
            $this->output_component("section-" . $section['id']);
        }
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
