<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../cms/CmsModel.php";
require_once __DIR__ . "/../cms/CmsController.php";
require_once __DIR__ . "/../cms/CmsComponent.php";
require_once __DIR__ . "/CmsInsertView.php";

/**
 * The cms component.
 */
class CmsInsertComponent extends CmsComponent
{
    /* Private Properties *****************************************************/

    private $acl;

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
     */
    public function __construct($services, $params)
    {
        $this->acl = $services['acl'];
        $model = new CmsModel($services, $params, "insert");
        $controller = new CmsController($model);
        $model->update_insert_properties();
        $view = new CmsInsertView($model, $controller);
        parent::__construct($model, $view, $controller);
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function to deny access on invalid pages and
     * sections.
     *
     * @retval bool
     *  True if the user has insert access to page, false otherwise
     */
    public function has_access()
    {
        $pid = $this->model->get_active_page_id();
        if(!$this->acl->has_access_insert($_SESSION['id_user'], $pid))
            return false;
        return parent::has_access();
    }
}
?>
