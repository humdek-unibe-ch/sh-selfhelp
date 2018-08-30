<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/UserModel.php";

/**
 * The base user component.
 */
class UserComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    protected $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor. It passes the view and controller instance to the
     * constructor of the parent class.
     *
     * @param object $model
     *  The model instance of the view component.
     * @param object $view
     *  The view instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $view, $controller=null)
    {
        $this->model = $model;
        parent::__construct($view, $controller);
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function of only display valid user ids.
     *
     * @retval bool
     *  True if the user exists, false otherwise
     */
    public function has_access()
    {
        if($this->model->get_uid() != null
            && $this->model->get_selected_user() == null)
            return false;
        return parent::has_access();
    }
}

