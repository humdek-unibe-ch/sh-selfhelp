<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the asset delete component.
 */
class AssetDeleteController extends BaseController
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param string $mode
     *  Specifies the insert mode (either 'css' or 'asset').
     */
    public function __construct($model, $mode)
    {
        parent::__construct($model);
        if(isset($_POST["rm_file"]) && $this->model->can_delete_asset())
        {
            $file = filter_var($_POST['rm_file'], FILTER_SANITIZE_STRING);
            $path = $this->model->get_server_path($mode) . '/' . $file;
            if(file_exists($path) && unlink($path))
                $this->success = true;
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Unable to delete the file from the server";
            }
        }
    }
}
?>
