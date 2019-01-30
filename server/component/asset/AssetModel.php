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

    /**
     * Checks whether the current user has the rights to delete assets.
     *
     * @retval bool
     *  True if the delete rights are granted, false otherwise.
     */
    public function can_delete_asset()
    {
        return $this->acl->has_access_delete($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("assetDelete"));
    }

    /**
     * Checks whether the current user has the rights to add new assets.
     *
     * @retval bool
     *  True if the insert rights are granted, false otherwise.
     */
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
    public function get_asset_files($mode)
    {
        $server_path = $this->get_server_path($mode);
        $base_url = $this->get_base_url($mode);
        $files = array();
        if($handle = opendir($server_path)) {
            while(false !== ($file = readdir($handle)))
            {
                if(filetype($server_path . '/' . $file) === "dir") continue;
                $files[] = $file;
            }
            closedir($handle);
        }
        natcasesort($files);
        $assets = array();
        foreach($files as $file)
            $assets[] = array("id" => $file, "title" => $file, "url" => $base_url . '/' . $file);
        return $assets;
    }

    public function get_server_path($mode)
    {
        if($mode === "css")
            return CSS_SERVER_PATH;
        else if($mode === "asset")
            return ASSET_SERVER_PATH;
    }

    public function get_base_url($mode)
    {
        if($mode === "css")
            return CSS_PATH;
        else if($mode === "asset")
            return ASSET_PATH;
    }
}
?>
