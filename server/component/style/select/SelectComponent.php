<?php
require_once __DIR__ . "/../formField/FormFieldComponent.php";
require_once __DIR__ . "/../formField/FormFieldModel.php";
require_once __DIR__ . "/SelectView.php";

/**
 * A component class for the select style component.
 * This component renders a select form field.
 */
class SelectComponent extends FormFieldComponent
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
        $model = new FormFieldModel($services, $id);
        $view = new SelectView($model);
        parent::__construct($model, $view);
    }
}
?>
