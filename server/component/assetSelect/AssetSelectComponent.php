<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/AssetSelectView.php";
require_once __DIR__ . "/../asset/AssetModel.php";

/**
 * The class to define the asset select component.
 */
class AssetSelectComponent extends BaseComponent
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
        $model = new AssetModel($services);
        $view = new AssetSelectView($model);
        parent::__construct($model, $view);
    }
}
?>
