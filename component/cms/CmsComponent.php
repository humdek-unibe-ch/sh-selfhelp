<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/CmsView.php";
require_once __DIR__ . "/CmsInsertView.php";
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
     * @param int $id_page
     *  The id of the page that is currently edited.
     * @param string $mode
     *  The mode of the page: 'select', 'update', 'insert', or 'delete'
     * @param int $id_root_section
     *  The root id of a page or the section that is currently selected.
     * @param int $id_section
     *  The id of the section that is currently selected (only relevant for
     *  navigation pages).
     */
    public function __construct($services, $id_page=null, $mode='select',
        $id_root_section=null, $id_section=null)
    {
        $model = new CmsModel($services, $id_page, $id_root_section,
            $id_section, $mode);
        $controller = new CmsController($model);
        if($mode == "select" || $mode == "update")
            $view = new CmsView($model, $controller);
        if($mode == "insert")
            $view = new CmsInsertView($model, $controller);
        parent::__construct($view);
    }
}
?>
