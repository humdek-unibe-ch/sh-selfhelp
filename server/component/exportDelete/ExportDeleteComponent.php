<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/ExportDeleteView.php";
require_once __DIR__ . "/ExportDeleteModel.php";
require_once __DIR__ . "/ExportDeleteController.php";

/**
 * The class to define the exportDelete component.
 */
class ExportDeleteComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'selector': The string to identify which data to remove.
     */
    public function __construct($services, $params)
    {
        $model = new ExportDeleteModel($services, $params['selector']);
        $controller = new ExportDeleteController($model);
        $view = new ExportDeleteView($model, $controller);
        parent::__construct($model, $view);
    }
}
?>
