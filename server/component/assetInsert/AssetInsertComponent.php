<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/AssetInsertView.php";
require_once __DIR__ . "/AssetInsertController.php";
require_once __DIR__ . "/../asset/AssetModel.php";

/**
 * The class to define the asset insert component.
 */
class AssetInsertComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class, the View class,
     * and the controller class and passes them to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new AssetModel($services);
        $controller = new AssetInsertController($model);
        $view = new AssetInsertView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
