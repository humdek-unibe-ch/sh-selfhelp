<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/InterestedUserFormView.php";
require_once __DIR__ . "/InterestedUserFormModel.php";
require_once __DIR__ . "/InterestedUserFormController.php";

/**
 * A component class for a interstedUserForm style component. This style is
 * intended to collect email addresses of interested users and send automated
 * emails to them.
 */
class InterestedUserFormComponent extends BaseComponent
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
        $model = new InterestedUserFormModel($services, $id);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new InterestedUserFormController($model);
        $view = new InterestedUserFormView($model, $controller);

        parent::__construct($model, $view, $controller);
    }
}
?>
