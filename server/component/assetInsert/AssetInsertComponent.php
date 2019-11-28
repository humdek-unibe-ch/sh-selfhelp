<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     * @param array $params
     *  The array of get parameters:
     *   - 'mode':  Specifies the insert mode (either 'css' or 'asset').
     */
    public function __construct($services, $params)
    {
        $mode = $params['mode'] ?? "";
        $model = new AssetModel($services);
        $controller = new AssetInsertController($model, $mode);
        $view = new AssetInsertView($model, $controller, $mode);
        parent::__construct($model, $view, $controller);
    }
}
?>
