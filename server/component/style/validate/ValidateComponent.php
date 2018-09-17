<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/ValidateView.php";
require_once __DIR__ . "/ValidateModel.php";
require_once __DIR__ . "/ValidateController.php";

/**
 * The user validation component. This component is intended for the user
 * validation once the user received an email with a validation link.
 */
class ValidateComponent extends BaseComponent
{
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
     * @param array $params
     */
    public function __construct($services, $id, $params)
    {
        $uid = isset($params['uid']) ? intval($params['uid']) : null;
        $token = isset($params['token']) ? intval($params['token']) : null;
        $this->has_params = ($uid != null && $token != null);
        $model = new ValidateModel($services, $id, $uid, $token);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new ValidateController($model);
        $view = new ValidateView($model, $controller);
        parent::__construct($model, $view, $controller);
    }

    /**
     * Redefine parent method. Access is only granted if a user id and a token
     * are provided.
     */
    public function has_access()
    {
        return (parent::has_access() && $this->has_params
            && $this->model->is_token_valid());
    }
}
?>
