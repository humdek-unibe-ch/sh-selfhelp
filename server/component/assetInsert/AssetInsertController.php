<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the asset insert component.
 */
class AssetInsertController extends BaseController
{
    /* Private Properties *****************************************************/

    /**
     * The name of the new asset.
     */
    private $name;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param string $mode
     *  Specifies the insert mode (either 'css', 'asset', or 'static').
     */
    public function __construct($model, $mode)
    {
        parent::__construct($model);
        $this->name = "";
        // check that post_max_size has not been reached
        if(!$model->can_insert_asset())
            return;
        if(isset($_SERVER['CONTENT_LENGTH'])
                && (int)$_SERVER['CONTENT_LENGTH']
                    > $this->convert_to_bytes(ini_get('post_max_size')))
        {
            $this->fail = true;
            $this->error_msgs[] = "The file size exceeds the maximal allowed upload size";
            return;
        }
        if(isset($_POST['name']) && isset($_FILES['file']))
        {
            $info = pathinfo($_FILES['file']['name']);
            $ext = $info['extension'];
            $res = $model->check_extension($ext, $mode);
            if($res['error'])
            {
                $this->fail = true;
                $this->error_msgs[] = $res['msg'];
                return;
            }
            $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $this->name = $name. '.' .$ext;
            $target = $model->get_server_path($mode) . '/' . $this->name;
            if(!isset($_POST['overwrite']) && file_exists($target))
            {
                $this->fail = true;
                $this->error_msgs[] = "A file with the same name already exists";
                return;
            }
            $res = $model->pp_insert_asset_file($mode,
                $_FILES['file']['tmp_name'], $name, isset($_POST['overwrite']));
            if($res !== true) {
                $this->fail = true;
                $this->error_msgs[] = $res;
                return;
            }
            if(move_uploaded_file($_FILES['file']['tmp_name'], $target))
            {
                $this->success = true;
            }
            else
            {
                $this->fail = true;
                $this->error_msgs[] = "Unable to store the file on the server";
                $res = $model->pp_delete_asset_file_static($name);
                return;
            }
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Return the name of the uploaded file.
     *
     * @return int
     *  The newly created group name.
     */
    public function get_new_name()
    {
        return $this->name;
    }
}
?>
