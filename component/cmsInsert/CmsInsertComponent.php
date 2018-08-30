<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../cms/CmsModel.php";
require_once __DIR__ . "/CmsInsertController.php";
require_once __DIR__ . "/CmsInsertView.php";

/**
 * The cms component.
 */
class CmsInsertComponent extends BaseComponent
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
        $controller = new CmsInsertController($model);
        $view = new CmsInsertView($model, $controller);
        parent::__construct($view, $controller);
    }
}
?>
