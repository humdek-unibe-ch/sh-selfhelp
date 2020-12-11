<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/TriggerView.php";
require_once __DIR__ . "/TriggerModel.php";
require_once __DIR__ . "/TriggerController.php";

/**
 * The class to define the asset select component.
 */
class TriggerComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The section id of this component.
     */
    public function __construct($services, $id)
    {
        $model = new TriggerModel($services, $id);
        $controller = new TriggerController($model);
        $view = new TriggerView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
