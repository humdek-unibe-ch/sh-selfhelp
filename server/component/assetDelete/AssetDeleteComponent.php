<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../asset/AssetModel.php";
require_once __DIR__ . "/AssetDeleteView.php";
require_once __DIR__ . "/AssetDeleteController.php";

/**
 * The asset delete component.
 */
class AssetDeleteComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The array of get parameters:
     *   - 'mode':  Specifies the insert mode (either 'css' or 'asset').
     *   - 'file':  Specifies the name of the file to be deleted.
     */
    public function __construct($services, $params)
    {
        $mode = $params['mode'] ?? "";
        $file = $params['file'] ?? "";
        $model = new AssetModel($services);
        $controller = new AssetDeleteController($model, $mode);
        $view = new AssetDeleteView($model, $controller, $mode, $file);
        parent::__construct($model, $view, $controller);
    }
}
?>
