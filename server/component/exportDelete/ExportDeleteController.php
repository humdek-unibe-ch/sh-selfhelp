<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the exportDelete component.
 */
class ExportDeleteController extends BaseController
{
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
        if(isset($_POST['veryfication']))
        {
            if($_POST['veryfication'] == $this->model->get_veryfication_str())
            {
                $res = $this->model->remove_all_data();
                if($res !== false)
                    $this->success = true;
                else
                {
                    $this->fail = true;
                    $this->error_msgs[] = "Failed to remove data.";
                }
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Failed to remove data: The verification text does not match.";
            }
        }
    }
}
?>

