<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/UserModel.php";

/**
 * The user component.
 */
class UserComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    protected $model;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CmsModel class, the UserView
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($model, $view, $controller=null)
    {
        $this->model = $model;
        parent::__construct($view, $controller);
    }

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

