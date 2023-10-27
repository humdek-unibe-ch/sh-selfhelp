<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/ModuleScheduledJobsView.php";
require_once __DIR__ . "/ModuleScheduledJobsModel.php";
require_once __DIR__ . "/ModuleScheduledJobsController.php";

/**
 * The class to define the asset select component.
 */
class ModuleScheduledJobsComponent extends BaseComponent
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
        $mqid = isset($params['sjid']) ? intval($params['sjid']) : null;
        $model = new ModuleScheduledJobsModel($services, $mqid);
        $controller = new ModuleScheduledJobsController($model);
        $view = new ModuleScheduledJobsView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
