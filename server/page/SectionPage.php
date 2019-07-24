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

    /**
     * The list of sections to be rendered on the page.
     */
    private $sections;

    /**
     * The id of the selceted navigation section.
     */
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
     * @param array $params
     *  An array of get parameter taht will be passed to each style component.
     *  If the page is a navigation page it must hold the key 'nav' where the
     *  value defines the id of the current navigation section.
     */
    public function __construct($router, $db, $keyword, $params=array())
    {
        parent::__construct($router, $db, $keyword);
        $this->nav_section_id = isset($params['nav']) ? $params['nav'] : null;

        $this->sections = $db->fetch_page_sections($keyword);
        foreach($this->sections as $section)
            $this->add_component("section-" . $section['id'],
                new StyleComponent($this->services, intval($section['id']),
                    $params));

        if($this->nav_section_id != null)
        {
            $this->services['nav']->set_current_index($this->nav_section_id);
            $this->add_component("navigation", new StyleComponent(
                $this->services, $this->id_navigation_section, $params));
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
        if($this->services['acl']->has_access($_SESSION['id_user'],
                $this->id_page, 'insert'))
        {
            $arr = array('pid' => $this->id_page);
            if($this->id_page == $_SESSION['cms_edit_url']['pid'])
                $arr = $_SESSION['cms_edit_url'];
            $url = $this->services['router']->generate('cmsSelect', $arr);
            require __DIR__ . "/tpl_cms_edit.php";
        }
        $was_section_rendered = false;
        foreach($this->sections as $section)
        {
            $comp = $this->get_component("section-" . $section['id']);
            if($comp->has_access())
            {
                $comp->output_content();
                $was_section_rendered = true;
            }
        }
        if($this->nav_section_id)
        {
            $sql = "SELECT * FROM sections_navigation
                WHERE child = :id AND id_pages = :pid";
            if($this->services['db']->query_db_first($sql, array(
                    ":id" => $this->nav_section_id, ":pid" => $this->id_page)))
            {
                $this->output_component("navigation");
                $was_section_rendered = true;
            }
        }

        if((count($this->sections) > 0 || $this->nav_section_id)
            && !$was_section_rendered)
        {
            $page = new InternalPage($this, "missing");
            $page->output_content();
        }
    }
}
?>
