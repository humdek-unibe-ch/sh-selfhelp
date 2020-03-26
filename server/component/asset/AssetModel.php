<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     * Check the extension given a mode.
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css', 'asset', 'static').
     * @param string $ext
     *  The file extension to check.
     * @retval boolean
     *  True on success, false on failure.
     */
    public function check_extension($ext, $mode)
    {
        $res = array(
            "error" => false,
            "msg" => ""
        );
        if($mode === "css")
        {
            $res['error'] = !(strtolower($ext) === "css");
            $res['msg'] = "Bad file extension '".$ext."', expecting 'css'";
        }
        else if($mode === "static")
        {
            $res['error'] = !(strtolower($ext) === "csv");
            $res['msg'] = "Bad file extension '".$ext."', expecting 'csv'";
        }
        return $res;
    }

    /**
     * Returns an array of asset files.
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css', 'asset', 'static').
     * @retval array
     *  An array of asset files where each file has the following keys:
     *   'title':   The name of the file.
     *   'id':      The index of the file.
     *   'url':     The url to the file.
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

    /**
     * Return the server path depending on the asset mode.
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css', 'asset', 'static').
     * @retval string
     *  The server path.
     */
    public function get_server_path($mode)
    {
        if($mode === "css")
            return CSS_SERVER_PATH;
        else if($mode === "asset")
            return ASSET_SERVER_PATH;
        else if($mode === "static")
            return STATIC_SERVER_PATH;
    }

    /**
     * Return the base url depending on the asset mode.
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css', 'asset', 'static').
     * @retval string
     *  The base url.
     */
    public function get_base_url($mode)
    {
        if($mode === "css")
            return CSS_PATH;
        else if($mode === "asset")
            return ASSET_PATH;
        else if($mode === "static")
            return STATIC_PATH;
    }
}
?>
