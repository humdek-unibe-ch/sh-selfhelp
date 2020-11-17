<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/CmsExportView.php";
require_once __DIR__ . "/CmsExportModel.php";
require_once __DIR__ . "/CmsExportController.php";

/**
 * The class to define the asset select component.
 */
class CmsExportComponent extends BaseComponent
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
    public function __construct($services, $params)
    {
        $id = intval($params['id']);
        $type = isset($params['type']) ? $params['type'] : '';
        $model = new CmsExportModel($services, $type, $id);
        $controller = new CmsExportController($model);
        $view = new CmsExportView($model, $controller);        
        parent::__construct($model, $view);
    }
}
?>
