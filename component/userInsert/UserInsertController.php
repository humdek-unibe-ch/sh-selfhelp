<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the user insert component.
 */
class UserInsertController extends BaseController
{
    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the cms component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if(!isset($_POST['mode'])) return;
        if($_POST['mode'] == "insert")
            $this->insert_new_user();
    }

    /* Private Methods ********************************************************/

    private function insert_new_user()
    {
        $groups = array();
        if(isset($_POST['user_groups'])) $groups = $_POST['user_groups'];
        if(isset($_POST['email']))
            $this->model->insert_new_user($_POST['email'], $groups);
    }

    /* Public Methods *********************************************************/

}
?>
