<?php
require_once __DIR__ . "/../cms/CmsModel.php";
require_once __DIR__ . "/../cms/CmsComponent.php";
require_once __DIR__ . "/CmsDeleteController.php";
require_once __DIR__ . "/CmsDeleteView.php";

/**
 * The cms delete component. It provides a component wrapper for the service of
 * deleting pages.
 */
class CmsDeleteComponent extends CmsComponent
{
    /* Private Properties *****************************************************/

    private $acl;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CmsModel class, the
     * CmsDeleteView class, and the CmsDeleteController class and passes the
     * view, controller, and model instances to the constructor of the parent
     * class.
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
     */
    public function __construct($services, $params)
    {
        $this->acl = $services['acl'];
        $model = new CmsModel($services, $params, "delete");
        $controller = new CmsDeleteController($model);
        $model->update_delete_properties();
        $view = new CmsDeleteView($model, $controller);
        parent::__construct($model, $view, $controller);
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function to deny access on invalid pages and
     * sections.
     *
     * @retval bool
     *  True if the user has delete access to page, false otherwise
     */
    public function has_access($skip_ids = false)
    {
        $pid = $this->model->get_active_page_id();
        $skip_ids = $this->controller->has_succeeded();
        if(!$skip_ids
            && !$this->acl->has_access_delete($_SESSION['id_user'], $pid))
            return false;
        return parent::has_access($skip_ids);
    }
}
?>
