<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the asset components such
 * that the data can easily be displayed in the view of the component.
 */
class AssetModel extends BaseModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
    }

    /* Public Methods *********************************************************/

    public function can_delete_asset()
    {
        return $this->acl->has_access_delete($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("assetDelete"));
    }

    public function can_insert_asset()
    {
        return $this->acl->has_access_insert($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("assetInsert"));
    }

    /**
     * Returns an array of asset files.
     *
     * @retval array
     *  An array of asset files where each file has the following keys:
     *   'title':   The name of the file.
     *   'id':      The index of the file.
     */
    public function get_asset_files()
    {
        $files = array();
        if($handle = opendir(ASSET_SERVER_PATH)) {
            while(false !== ($file = readdir($handle)))
            {
                if(filetype(ASSET_SERVER_PATH . '/' . $file) === "dir") continue;
                $files[] = $file;
            }
            closedir($handle);
        }
        natcasesort($files);
        foreach($files as $file)
            $assets[] = array("id" => $file, "title" => $file);
        return $assets;
    }
}
?>
