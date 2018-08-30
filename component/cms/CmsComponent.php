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
     * The constructor. It passes the view and controller instance to the
     * constructor of the parent class.
     *
     * @param object $model
     *  The model instance of the view component.
     * @param object $view
     *  The view instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $view, $controller = null)
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
    public function has_access($skip_ids = false)
    {
        $sections = $this->model->get_page_sections();
        $params = $this->model->get_current_url_params();
        if(!$skip_ids
            && ((($params['ssid'] != null)
                && !$this->is_section_in_list($params['ssid'], $sections))
            || ($params['sid'] != null
                && !$this->is_section_in_list($params['sid'], $sections))
            || ($params['did'] != null
                && !$this->is_section_in_list($params['did'], $sections))))
            return false;
        return parent::has_access();
    }
}
?>
