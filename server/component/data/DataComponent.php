<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/DataModel.php";
require_once __DIR__ . "/DataView.php";
require_once __DIR__ . "/DataController.php";

/**
 * The data component - visualize user inputs
 */
class DataComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the DataModel class and the
     * DataView class and passes them to the
     * constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'uid':     The id of the selected user. 
     */
    public function __construct($services, $params)
    {
        $uid = isset($params['uid']) ? $params['uid'] : null;
        $model = new DataModel($services, $uid);
        $controller = new DataController($model);
        $view = new DataView($model, $controller);
        parent::__construct($model, $view, null);
    }
}
