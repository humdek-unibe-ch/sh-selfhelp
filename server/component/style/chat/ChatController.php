<?php
require_once __DIR__ . "/../../BaseController.php";
/**
 * The controller class of the chat component.
 */
class ChatController extends BaseController
{
    /* Private Properties *****************************************************/

    private $fail;

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

    /* Public Methods *********************************************************/

    /**
     * Returns the failure status
     *
     * @retval bool
     *  true if the operation has failed, false otherwise.
     */
    public function has_failed()
    {
        return $this->fail;
    }
}
?>
