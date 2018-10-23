<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/FormLogView.php";
require_once __DIR__ . "/../formBase/FormBaseModel.php";
require_once __DIR__ . "/../formBase/FormBaseController.php";

/**
 * A component class for a formLog style component. This style is intended to
 * handle journal user input. Journal user input is stored to the database
 * together with a timestamp and cannot be changed afterwards.
 */
class FormLogComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this navigation component.
     */
    public function __construct($services, $id)
    {
        $model = new FormBaseModel($services, $id);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new FormBaseController($model, true);
        $view = new FormLogView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
