<?php
require_once __DIR__ . "/../BaseComponent.php";

/**
 * The cms component.
 */
class CmsComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    protected $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CmsModel class, the CmsView
     * class, and the CmsController class and passes the view instance to the
     * constructor of the parent class.
     *
     */
    public function __construct($model, $view, $controller)
    {
        $this->model = $model;
        parent::__construct($view, $controller);
    }

    /* Private Methods ********************************************************/

    /**
     * Checks whether a section is in a hierarchical list of sections.
     *
     * @param int $id_section
     *  The id of the section to check.
     * @param array $sections
     *  A list of sections.
     */
    private function is_section_in_list($id_section, $sections)
    {
        foreach($sections as $section)
        {
            if($this->is_section_in_list($id_section, $section['children']))
                return true;
            if($section['id'] == $id_section) return true;
        }
        return false;
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function to deny access on invalid pages and
     * sections.
     *
     * @retval bool
     *  True if the user the page or section exists, false otherwise
     */
    public function has_access()
    {
        $sections = $this->model->get_page_sections();
        $params = $this->model->get_current_url_params();
        if(($params['ssid'] != null
                && !$this->is_section_in_list($params['ssid'], $sections))
            || ($params['sid'] != null
                && !$this->is_section_in_list($params['sid'], $sections))
            || ($params['did'] != null
                && !$this->is_section_in_list($params['did'], $sections)))
            return false;
        return parent::has_access();
    }
}
?>
