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
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->name = "";
        if(isset($_POST['name']) && isset($_FILES['file']))
        {
            $info = pathinfo($_FILES['file']['name']);
            $ext = $info['extension'];
            $this->name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $this->name = $this->name. '.' .$ext;
            $target = ASSET_SERVER_PATH . '/' . $this->name;
            if(file_exists($target))
            {
                $this->fail = true;
                return;
            }
            if(move_uploaded_file($_FILES['file']['tmp_name'], $target))
                $this->success = true;
            else
                $this->fail = true;
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
