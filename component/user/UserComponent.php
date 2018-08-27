<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/UserView.php";
require_once __DIR__ . "/UserModel.php";

/**
 * The user component.
 */
class UserComponent extends BaseComponent
{
    /* Private Properties *****************************************************/

    private $model;
    private $uid;

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
    public function __construct($services, $params)
    {
        $this->uid = null;
        if(isset($params['uid'])) $this->uid = $params['uid'];
        $this->model = new UserModel($services, $this->uid);
        $view = new UserView($this->model);
        parent::__construct($view);
    }

    public function has_access()
    {
        if($this->uid != null && $this->model->get_selected_user() == null)
            return false;
        return parent::has_access();
    }
}
?>
