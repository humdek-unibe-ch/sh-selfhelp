<?php
require_once __DIR__ . "/../BaseController.php";

/**
 * The controller class of the email component.
 */
class EmailController extends BaseController
{
    /* Private Properties *****************************************************/

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
        $success = true;
        foreach($_POST as $key => $field)
        {
            $ids = explode("-", $key);
            if(count($ids) !== 3)
            {
                $this->fail = true;
                $this->error_msgs[] = "Cannot parse the given keys";
                return;
            }
            $id_page = filter_var($ids[0], FILTER_SANITIZE_NUMBER_INT);
            $id_field = filter_var($ids[1], FILTER_SANITIZE_NUMBER_INT);
            $id_lang = filter_var($ids[2], FILTER_SANITIZE_NUMBER_INT);
            if(!$this->model->update_email($id_page, $id_field, $id_lang, $field))
                $success = false;
        }
        if($success)
        {
            $this->success = true;
            $this->success_msgs[] = "Sucessfully updated the emails";
        }
        else
        {
            $this->fail = true;
            $this->error_msgs[] = "Cannot update the email in the database";
        }
    }

    /* Public Methods *********************************************************/
}
?>
