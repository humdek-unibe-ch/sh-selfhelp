<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/../component/navSection/NavSectionComponent.php";
require_once __DIR__ . "/../component/style/StyleComponent.php";

/**
 * This class maps the section structure of the DB. A section page consists
 * solely of a collection of sections as defined in the database.
 */
class NavigationPage extends BasePage
{
    /* Private Properties *****************************************************/

    private $sections;
    private $section_id;

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
    public function __construct($router, $db, $keyword, $id)
    {
        parent::__construct($router, $db, $keyword);
        if($id == null) return;

        $this->section_id = $id;
        $this->sections = $db->fetch_page_sections($this->keyword);
        $nav_id = $this->get_nav_id();
        if($nav_id == false)
            throw new Exception("Trying to create a navigation page without associating a navigation section.");
        $this->services["nav"] = new NavSectionComponent($this->services,
            $nav_id, $id);
        $this->add_component("section-navigation", $this->services["nav"]);
    }

    /* Private Methods ********************************************************/

    /**
     * Returns the id of the navigation section.
     *
     * @retval int
     *  The id of the navigation section or false if no id was found.
     */
    private function get_nav_id()
    {
        foreach($this->sections as $section)
            if(intval($section['id_styles']) == NAVIGATION_STYLE_ID)
                return intval($section['id']);
        return false;
    }

    /**
     * Returns true if the current section id can be found in the list of
     * sections associated to the navigation page.
     *
     * @retval bool
     *  True if the id exists, false otherwise.
     */
    private function section_id_exists()
    {
        foreach($this->sections as $section)
            if(intval($section['id']) == $this->section_id)
                return true;
        return false;
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content()
    {
        $this->output_component("section");
    }

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}

    /* Public Methods *********************************************************/

    /**
     * Adds the component to be displayed on the page. If no component is
     * provided a style component is created (see class StyleComponent).
     * Before the component is added, it is checked whether the section id
     * can be found in the sections associated to the navigation page.
     *
     * @param object $component
     *  A component instance which will be added to the page component list.
     *  If no component is set, by default a style component is created.
     */
    public function add_navigation_component($component = null)
    {
        if($this->section_id_exists())
        {
            if($component == null)
                $component = new StyleComponent($this->services,
                    $this->section_id);
            $this->add_component("section", $component);
        }
        else
            $this->add_component("section", null);
    }
}
?>
