<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/ModuleScheduledJobsCalendarView.php";
require_once __DIR__ . "/ModuleScheduledJobsCalendarModel.php";
require_once __DIR__ . "/../moduleScheduledJobs/ModuleScheduledJobsController.php";

/**
 * The class to define the asset select component.
 */
class ModuleScheduledJobsCalendarComponent extends BaseComponent
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
        $uid = isset($params['uid']) ? $params['uid'] : null;
        $model = new ModuleScheduledJobsCalendarModel($services, $uid);
        $view = new ModuleScheduledJobsCalendarView($model);
        parent::__construct($model, $view);
    }
}
?>