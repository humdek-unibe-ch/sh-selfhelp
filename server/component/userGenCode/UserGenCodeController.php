<?php
require_once __DIR__ . "/../user/UserController.php";
/**
 * The controller class of the validation code generation component.
 */
class UserGenCodeController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * The number of requested codes
     */
    private $count;

    /**
     * The number of generated codes
     */
    private $final_count;

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
        if(isset($_POST['count']))
        {
            $this->count = filter_var($_POST['count'], FILTER_SANITIZE_NUMBER_INT);
            if(!$this->count)
            {
                $this->fail = true;
                $this->error_msgs[] = "Invalid user count.";
                return;
            }
            if($this->count > MAX_USER_COUNT)
            {
                $this->fail = true;
                $this->error_msgs[] = "Please select a value that is no more than " . MAX_USER_COUNT .".";
                return;
            }
            $this->final_count = $this->model->generate_codes($this->count);
            if($this->final_count > 0)
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to generate validation codes.";
            }
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Return the number of requested codes.
     *
     * @return int
     *  The number of requested codes.
     */
    public function get_requested_count()
    {
        return $this->count;
    }

    /**
     * Return the number of created codes.
     *
     * @return int
     *  The number of created codes.
     */
    public function get_final_count()
    {
        return $this->final_count;
    }
}
?>
