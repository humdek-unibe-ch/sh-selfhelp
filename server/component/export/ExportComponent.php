<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/ExportView.php";
require_once __DIR__ . "/ExportModel.php";

/**
 * The class to define the export component.
 */
class ExportComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new ExportModel($services);
        $view = new ExportView($model);
        parent::__construct($model, $view);
    }
}
?>
