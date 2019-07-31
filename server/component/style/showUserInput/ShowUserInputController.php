<?php
require_once __DIR__ . "/../../BaseController.php";

/**
 * The controller class of showUserInput style component.
 */
class ShowUserInputController extends BaseController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(count($_POST) === 0) return;
        if(!isset($_POST['user_input_remove_id']))
            return;

        $ids = explode(',', $_POST['user_input_remove_id']);
        foreach($ids as $id)
            $this->model->mark_user_input_as_removed(intval($id));
        unset($_POST['user_input_remove_id']);
    }
}
?>
