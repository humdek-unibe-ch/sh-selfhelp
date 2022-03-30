<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/../formUserInput/FormUserInputView.php";
require_once __DIR__ . "/../formUserInput/FormUserInputModel.php";
require_once __DIR__ . "/../formUserInput/FormUserInputController.php";

/**
 * A component class for a formUserInput style component. This style is intended
 * to handle user input.
 *
 * - Persisten user input is stored to the database and made availabile to the
 * user for continuous modification.
 * - Journal user input is stored to the database together with a timestamp and
 * cannot be changed afterwards.
 */
class FormUserInputRecordComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this navigation component.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        $record_id = isset($params['record_id']) ? intval($params['record_id']) : -1;
        $router = $services->get_router();
        $router_params = $router->route['params'];
        if($record_id == -1){
            $record_id = isset($router_params['record_id']) ? intval($router_params['record_id']) : -1;
        }
        $model = new FormUserInputModel($services, $id, $params, $id_page, $entry_record);        
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new FormUserInputController($model, $record_id);
        $view = new FormUserInputView($model, $controller, $record_id);
        parent::__construct($model, $view, $controller);
        if ($this->controller && !isset($_POST[ENTRY_RECORD_ID])) {
            // dont execute it if it is from entry; It will be handled later
            $this->controller->execute();
        }
    }
}
?>
