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

    /* Private Methods ********************************************************/

    /**
     * Postprocessing DB data after deleting a static data file. The
     * corresponding DB-entries are removed.
     *
     * @param string $name
     *  The name of the file (without extension)
     * @retval mixed
     *  True on success, an error message on failure.
     */
    public function pp_delete_asset_file_static($name)
    {
        $res = $this->db->remove_by_fk("uploadTables", "name", $name);
        if(!$res) {
            return "postprocess: failed to remove old data values";
        }
        return true;
    }

    /**
     * Postprocessing data after uploading a static data file. The uploaded
     * file is parsed and the content is stored to the database.
     *
     * @param string $path
     *  The path to the uploaded file
     * @param string $name
     *  The name of the file (without extension)
     * @param boolean $overwrite
     *  If set to true, a table with the same name should already exist and
     *  will be removed bevore the new values will be added. If set to false
     *  the new table must not yet exist.
     * @retval mixed
     *  True on success, an error message on failure.
     */
    private function pp_insert_asset_file_static($path, $name, $overwrite)
    {
        $fh = fopen($path, 'r');
        if(!$fh) {
            return "postprocess: failed to open the uploaded file";
        }
        $sql = "SELECT * FROM uploadTables WHERE `name` = :tbl_name";
        $has_table = $this->db->query_db_first($sql, array("tbl_name" => $name));

        if(!$overwrite && $has_table) {
            fclose($fh);
            return "postprocess: table with the same name already exists";
        }

        if($overwrite && $has_table) {
            $res = $this->pp_delete_asset_file_static($name);
            if($res !== true) {
                fclose($fh);
                return $res;
            }
        }

        $id_table = $this->db->insert("uploadTables", array(
            "name" => $name
        ));
        if(!$id_table) {
            fclose($fh);
            return "postprocess: failed to create new data table";
        }

        $col_ids = array();
        $head = fgetcsv( $fh );
        $db_data = array();
        foreach($head as $col) {
            $id_col = $this->db->insert("uploadCols", array(
                "name" => $col,
                "id_uploadTables" => $id_table
            ));
            if(!$id_col) {
                fclose($fh);
                return "postprocess: failed to add table cols";
            }
            array_push($col_ids, $id_col);
        }

        while(($data = fgetcsv( $fh )) !== false) {
            $id_row = $this->db->insert("uploadRows", array(
                "id_uploadTables" => $id_table
            ));
            if(!$id_row) {
                fclose($fh);
                return "postprocess: failed to add table rows";
            }
            $db_data = array();
            foreach($data as $idx => $val) {
                array_push($db_data, array($id_row, $col_ids[$idx], $val));
            }
            $res = $this->db->insert_mult("uploadCells",
                array(
                    "id_uploadRows",
                    "id_uploadCols",
                    "value"
                ), $db_data
            );
            if(!$res) {
                fclose($fh);
                return "postprocess: failed to add data values";
            }
        }
        fclose($fh);
        return true;
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

    /**
     * Postprocessing data after asset deletion
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css', 'asset', 'static').
     * @param string $name
     *  The name of the file (without extension)
     * @retval mixed
     *  True on success, an error message on failure.
     */
    public function pp_delete_asset_file($mode, $name)
    {
        if($mode === "static")
            return $this->pp_delete_asset_file_static($name);
        return true;
    }

    /**
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css', 'asset', 'static').
     * @param string $path
     *  The path to the uploaded file
     * @param string $name
     *  The name of the file (without extension)
     * @param boolean $overwrite
     *  If set to true the upload file was set to be overwritten if it already
     *  existsr. If set to false the new file must not yet exist.
     * @retval mixed
     *  True on success, an error message on failure.
     */
    public function pp_insert_asset_file($mode, $path, $name, $overwrite)
    {
        if($mode === "static")
            return $this->pp_insert_asset_file_static($path, $name, $overwrite);
        return true;
    }

    /**
     * Save an entry in table assets when a file is uploaded
     * @param object $asset
     * Information for the asset
     * @return boolean
     * Return the results of the operation
     */
    public function save_asset_db($asset)
    {
        $asset['id_assetTypes'] = $this->db->get_lookup_id_by_value(assetTypes, $asset['id_assetTypes']);
        $exist = $this->db->query_db_first('SELECT id FROM assets WHERE `file_name` = :file_name', array(":file_name" => $asset['file_name']));
        if ($exist) {
            // update
            return $this->db->update_by_ids('assets', $asset, array('id' => $exist['id']));
        } else {
            // insert
            return $this->db->insert("assets", $asset);
        }
    }

    /**
     * Delete the file entry from the assets table in the  DB
     * @param object $asset
     * Information for the asset
     * @return boolean
     * Return the results of the operation
     */
    public function delete_asset_db($asset)
    {
        $exist = $this->db->query_db_first('SELECT id FROM assets WHERE `file_name` = :file_name', array(":file_name" => $asset['file_name']));
        if ($exist) {
            return $this->db->remove_by_ids("assets", array("file_name" => $asset['file_name']));
        }
        return true; // the file is not in the DB        
    }

}
?>
