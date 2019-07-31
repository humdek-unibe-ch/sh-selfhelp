<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/../emailFormBase/EmailFormBaseView.php";
require_once __DIR__ . "/../emailFormBase/EmailFormBaseController.php";
require_once __DIR__ . "/EmailFormModel.php";

/**
 * A component class for a emailForm style component. This style is
 * intended to collect email addresses of interested users and send automated
 * emails to them.
 */
class EmailFormComponent extends BaseComponent
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
     *  The section id of this component.
     */
    public function __construct($services, $id)
    {
        $model = new EmailFormModel($services, $id);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new EmailFormBaseController($model);
        $view = new EmailFormBaseView($model, $controller);

        parent::__construct($model, $view, $controller);
    }
}
?>
