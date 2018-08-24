<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the user delete component.
 */
class UserDeleteController extends BaseController
{
    /* Private Properties *****************************************************/

    private $success;
    private $fail;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the user delete component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->success = false;
        $this->fail = false;
        $this->selected_user = $this->model->get_selected_user();
        if(isset($_POST['email']))
        {
            if($_POST['email'] == $this->selected_user['email'])
            {
                $res = $this->model->delete_user($this->selected_user['id']);
                if($res)
                    $this->success = true;
                else
                    $this->fail = true;
            }
            else
                $this->fail = true;
        }
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    public function has_succeeded()
    {
        return $this->success;
    }

    public function has_failed()
    {
        return $this->fail;
    }

}
?>
