<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
            $file = filter_var($_POST['rm_file'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $path = $this->model->get_server_path($mode) . '/' . $file;
            if(!file_exists($path))
            {
                $this->fail = true;
                $this->error_msgs[] = "Unable to delte file: No such file on the server";
                return;
            }
            if(!unlink($path))
            {
                $this->fail = true;
                $this->error_msgs[] = "Unable to delete the file from the server";
                return;
            }
            $info = pathinfo($file);
            $res = $model->pp_delete_asset_file($mode, $info['filename']);
            if($res !== true) {
                $this->fail = true;
                $this->error_msgs[] = "File was removed but data postprocessing failed";
                return;
            }
            if (!$model->delete_asset_db(array("file_name" => $file))) {
                $this->fail = true;
                $this->error_msgs[] = "File was not removed from the DB!";
                return;
            };
            $this->success = true;
        }
    }
}
?>
