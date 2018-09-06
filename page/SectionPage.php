<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/InternalPage.php";
require_once __DIR__ . "/../component/style/StyleComponent.php";
require_once __DIR__ . "/../component/style/BaseStyleComponent.php";

/**
 * This class maps the section structure of the DB. A section page consists
 * solely of a collection of sections as defined in the database.
 */
class SectionPage extends BasePage
{
    /* Private Properties *****************************************************/

    private $sections;
    private $nav_section_id;

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
    public function __construct($router, $db, $keyword, $params=array())
    {
        parent::__construct($router, $db, $keyword);
        $this->nav_section_id = isset($params['id']) ? $params['id'] : null;

        $this->sections = $db->fetch_page_sections($keyword);
        foreach($this->sections as $section)
            $this->add_component("section-" . $section['id'],
                new StyleComponent($this->services, intval($section['id'])));

        if($this->nav_section_id != null)
        {
            $this->services['nav']->set_current_index($this->nav_section_id);
            $this->add_component("navigation", new StyleComponent(
                $this->services, $this->id_navigation_section,
                $this->nav_section_id));
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
        $was_section_rendered = true;
        foreach($this->sections as $section)
        {
            $comp = $this->get_component("section-" . $section['id']);
            if($comp->has_access())
                $comp->output_content();
            else if(DEBUG)
                $was_section_rendered = false;
        }
        if($this->nav_section_id)
        {
            $sql = "SELECT * FROM sections_navigation
                WHERE child = :id AND id_pages = :pid";
            if($this->services['db']->query_db_first($sql, array(
                    ":id" => $this->nav_section_id, ":pid" => $this->id_page)))
                $this->output_component("navigation");
            else
                $was_section_rendered = false;
        }

        if((count($this->sections) > 0 || $this->nav_section_id)
            && !$was_section_rendered)
        {
            $page = new InternalPage($this, "missing");
            $page->output_content();
        }
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}
}
?>
