<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/CmsView.php";
require_once __DIR__ . "/CmsInsertView.php";
require_once __DIR__ . "/CmsDeleteView.php";
require_once __DIR__ . "/CmsUnknownView.php";
require_once __DIR__ . "/CmsModel.php";
require_once __DIR__ . "/CmsController.php";

/**
 * The cms component.
 */
class CmsComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CmsModel class, the CmsView
     * class, and the CmsController class and passes the view instance to the
     * constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'pid':     The id of the page that is currently edited.
     *   'sid':     The root id of a page or the section that is currently
     *              selected.
     *   'ssid':    The id of the section that is currently selected
     *              (only relevant for navigation pages).
     *   'did':     The id of a section to be deleted (only relevant in delete
     *              mode).
     *   'type':    This describes the database relation in order to know wheter
     *              to access pages, sections, navigations.
     * @param string $mode
     *  The mode of the page: 'select', 'update', 'insert', or 'delete'
     */
    public function __construct($services, $params, $mode='select')
    {
        if($params == null) $params = array("pid" => null,
            "sid" => null, "ssid" => null, "did" => null, "type" => null );
        $model = new CmsModel($services, $params, $mode);
        $controller = new CmsController($model);
        $model->update_select_properties();
        $sections = $model->get_page_sections();
        if(($params['ssid'] != null
                && !$this->is_section_in_list($params['ssid'], $sections))
            || ($params['sid'] != null
                && !$this->is_section_in_list($params['sid'], $sections)))
            $view = new CmsUnknownView($model);
        else if($mode == "select" || $mode == "update")
            $view = new CmsView($model, $controller);
        else if($mode == "insert")
            $view = new CmsInsertView($model, $controller);
        else if($mode == "delete")
            $view = new CmsDeleteView($model, $controller);
        parent::__construct($view);
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
}
?>
