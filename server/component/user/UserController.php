<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The base controller class of the user component.
 */
class UserController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * An array of user properties (see UserModel::fetch_user).
     */
    protected $selected_user;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->success = false;
        $this->fail = false;
        $this->selected_user = $this->model->get_selected_user();
    }
}
?>
