<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The base controller class of the user component.
 */
class UserController extends BaseController
{
    /* Private Properties *****************************************************/

    protected $success;
    protected $fail;
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

    /* Public Methods *********************************************************/

    /**
     * Returns true if the the operation was successful.
     *
     * @retval bool
     *  True if the operation was successful, false if no successful operation
     *  was performed.
     */
    public function has_succeeded()
    {
        return $this->success;
    }

    /**
     * Returns true if the the operation failed.
     *
     * @retval bool
     *  True if the operation failed, false if no failed operation was
     *  performed.
     */
    public function has_failed()
    {
        return $this->fail;
    }

}
?>
