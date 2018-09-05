<?php
require_once __DIR__ . "/../component/style/StyleComponent.php";

/**
 * This class is used to render standard pages such as 'missing', 'no_access',
 * etc. It requires an instance of a base page.
 */
class InternalPage
{
    /* Private Properties *****************************************************/

    private $sections;
    private $base;

    /* Constructors ***********************************************************/

    /**
     * The constructor of this class. It collects all sections that are
     * allocated to the current page. For each section, a StyleComponent is
     * created and added to the component list of the page.
     *
     * @param object $base
     *  The instance of a base page class.
     * @param string $keyword
     *  The identification name of the page.
     */
    public function __construct($base, $keyword)
    {
        $this->base = $base;
        $services = $this->base->get_services();
        $this->sections = $services['db']->fetch_page_sections($keyword);
        foreach($this->sections as $section)
        {
            $this->base->add_component("section-" . $section['id'],
                new StyleComponent($services, intval($section['id'])));
        }
    }

    /* Public Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all style
     * sections that are assigned to the current page (as specified in the
     * DB).
     */
    public function output_content()
    {
        foreach($this->sections as $section)
        {
            $this->base->output_component("section-" . $section['id']);
        }
    }
}
?>
