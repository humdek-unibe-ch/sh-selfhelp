<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the chat component.
 */
class ChatController extends BaseController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->fail = false;
        if(isset($_POST['msg']))
        {
            if(!$this->model->send_chat_msg(
                filter_var($_POST['msg'], FILTER_SANITIZE_STRING)))
                $this->fail = true;
        }
    }
}
?>
