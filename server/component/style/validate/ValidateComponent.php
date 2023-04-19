<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/ValidateView.php";
require_once __DIR__ . "/ValidateModel.php";
require_once __DIR__ . "/ValidateController.php";
require_once __DIR__ . "/../formUserInput/FormUserInputModel.php";
require_once __DIR__ . "/../formUserInput/FormUserInputController.php";

/**
 * The user validation component. This component is intended for the user
 * validation once the user received an email with a validation link.
 */
class ValidateComponent extends BaseComponent
{
    /**
     * A boolean value indicating whether the required parameters are available.
     */
    private $has_params;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the ValidateModel class, the
     * ValidateView class, and the ValidateController class and passes the view
     * and controller instance to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param array $params
     *  An array of get parameters. This component requires the following keys:
     *   'uid':     The user id of the user to be validated
     *   'token':   The validation token which has to match with the user id
     */
    public function __construct($services, $id, $params)
    {
        $uid = isset($params['uid']) ? intval($params['uid']) : null;
        $token = isset($params['token']) ? $params['token'] : null;
        $this->has_params = ($uid != null && $token != null);
        $model = new ValidateModel($services, $id, $uid, $token);
        $controller = null;
        $ui_controller = null;
        if(!$model->is_cms_page())
        {
            $uid_session = $_SESSION['id_user'];
            $_SESSION['id_user'] = $uid;
            $ui_model = new FormUserInputModel($services, $id, $params, -1, array());
            $ui_controller = new FormUserInputController($ui_model, -1);
            $ui_controller->execute();
            $_SESSION['id_user'] = $uid_session;
            if(!$ui_controller->has_failed())
                $controller = new ValidateController($model);
        }
        $view = new ValidateView($model, $controller, $ui_controller);
        parent::__construct($model, $view, $controller);
    }

    /**
     * Redefine parent method. Access is only granted if a user id and a valid
     * token is provided.
     */
    public function has_access()
    {
        if($this->model->is_cms_page())
            return parent::has_access();
        if(!$this->has_params || !$this->model->is_token_valid())
            return false;
        return parent::has_access();
    }
}
?>
