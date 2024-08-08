<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseAjax.php";

/**
 * A small class to allow to fetch internal or external  data from the DB. This
 * class is used for AJAX calls.
 */
class AjaxDataSource extends BaseAjax
{
    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        parent::__construct($services);
    }

    /* Private Methods ********************************************************/

    /**
     * Check wether a row of data passes the filter or not. If a filter name is
     * not recognised in a data item, the filter is ignored.
     *
     * @param array $filters
     *  An assoziative array of filters where the key corresponds to the name
     *  of the column to be filtered and the value is an array of value items.
     *  One value item has two keys:
     *   - `op` which defines teh comparing operation (`=`, `<`, `<=`, `>`,
     *     `>=`)
     *   - `val` is the value to compare to
     *  Filter names are combined with a logical AND while items within a filter
     *  name are combined with a logical OR.
     * @param $item
     *  A row of data where each key corresponds to the column name.
     * @retval boolean
     *  True if the item passed the filter, false otherwise.
     */
    private function check_filter_data($filters, $item) {
        foreach($filters as $name => $filter) {
            if(!isset($item[$name]))
                continue;
            $res_or = false;
            foreach($filter as $val) {
                if($val['op'] === "=") {
                    if($item[$name] == $val['val']) {
                        $res_or = true;
                        break;
                    }
                } else if($val['op'] === "<") {
                    if($item[$name] < $val['val']) {
                        $res_or = true;
                        break;
                    }
                } else if($val['op'] === "<=") {
                    if($item[$name] <= $val['val']) {
                        $res_or = true;
                        break;
                    }
                } else if($val['op'] === ">") {
                    if($item[$name] > $val['val']) {
                        $res_or = true;
                        break;
                    }
                } else if($val['op'] === ">=") {
                    if($item[$name] >= $val['val']) {
                        $res_or = true;
                        break;
                    }
                }
            }
            if($res_or === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Read external data from the database. This data is collected through a CSV
     * file upload.
     *
     * @param number $table_id
     *  The id of the uploaded CSV table.
     * @param array $filters
     *  An assoziative array of filters. Refer to
     *  AjaxDataSource::check_filter_data() for more information.
     * @retval array
     *  Returns a list of assiciative arrays items. Each item corresponds to
     *  a row of the data table. The keys of each item correspond to the column
     *  names of the table.
     */
    private function fetch_data_table($table_id, $filters)
    {
        $res = array();
        $sql = "SELECT * FROM view_dataTables_data
            WHERE table_id = :id
            ORDER BY row_id";
        $res_db = $this->db->query_db($sql, array(
            "id" => $table_id
        ));
        $last_row_id = $res_db[0]['row_id'];
        $item = array();
        foreach($res_db as $item_db) {
            if($item_db['row_id'] !== $last_row_id) {
                if($this->check_filter_data($filters, $item)) {
                    array_push($res, $item);
                }
                $item = array();
                $last_row_id = $item_db['row_id'];
            }
            $item[$item_db['col_name']] = $item_db['value'];
        }
        if($this->check_filter_data($filters, $item)) {
            array_push($res, $item);
        }
        return $res;
    }

    /**
     * Store a data filter value to the session.
     *
     * @param string $data_source
     *  The name of the data source table
     * @param string $filter_name
     *  The name of the column to be filtered
     * @param number $filter_idx
     *  The index of the filter value
     * @param string $filter_value
     *  The filter value
     */
    private function set_data_filter_value($data_source, $filter_name,
            $filter_idx, $filter_value) {
        if(!isset($_SESSION['data_filter']))
            $_SESSION['data_filter'] = array();
        if(!isset($_SESSION['data_filter'][$data_source]))
            $_SESSION['data_filter'][$data_source] = array();
        if(!isset($_SESSION['data_filter'][$data_source][$filter_name]))
            $_SESSION['data_filter'][$data_source][$filter_name] = array();
        $_SESSION['data_filter'][$data_source][$filter_name][$filter_idx] = $filter_value;
    }

    /**
     * Remove a data filter value from the session.
     *
     * @param string $data_source
     *  The name of the data source table
     * @param string $filter_name
     *  The name of the column to be filtered
     * @param number $filter_idx
     *  The index of the filter value
     */
    private function unset_data_filter_value($data_source, $filter_name,
            $filter_idx) {
        if(isset($_SESSION['data_filter'][$data_source][$filter_name][$filter_idx])) {
            unset($_SESSION['data_filter'][$data_source][$filter_name][$filter_idx]);
            if(count($_SESSION['data_filter'][$data_source][$filter_name]) === 0) {
                unset($_SESSION['data_filter'][$data_source][$filter_name]);
            }
        }
    }

    /* Public Methods *********************************************************/

    /**
     * The search function which can be called by an AJAX call.
     * 
     * This page should be created so the acl could be assigned. -> example for the url --> /request/[AjaxDataSource:class]/[get_data_table:method]/[v:table] 
     * the table is the chosen data source
     *
     * @retval array
     *  An array of user items where each item has the following keys:
     *   - 'value':     The email of the user.
     *   - 'id':        The id of the user.
     */
    public function get_data_table()
    {
        $table_name = $this->router->route['params']['table'];
        $sql = "SELECT * FROM view_dataTables WHERE `name` = :name";
        $source = $this->db->query_db_first($sql,
            array("name" => $table_name));
        $filter = array();
        if(isset($_SESSION['data_filter'][$table_name])
                && count($_SESSION['data_filter'][$table_name]) > 0) {
                $filter = $_SESSION['data_filter'][$table_name];
        }
        return $source ? $this->fetch_data_table($source['id'], $filter) : array();
    }

    /**
     * Update the active data filter in the session
     *
     * @param $data
     *  The POST data of the ajax call:
     *   - `action`:      `add` to add a filter, `rm` to remove a filter.
     *   - `name`:        the name of the filter to be added or removed.
     *   - `data_source`: the name of the data to fetch.
     *   - `value_idx`:   the index of the filter value.
     *   - `value`:       teh filter value.
     * @retval array
     *  The currently set filters in the session
     */
    public function set_data_filter($data)
    {
        if($data['action'] === "add") {
            $this->set_data_filter_value($data['data_source'], $data['name'],
                $data['value_idx'], $data['value']);
        } else if($data['action'] === "rm") {
            $this->unset_data_filter_value($data['data_source'], $data['name'],
                $data['value_idx']);
        }
        return $_SESSION['data_filter'];
    }

    /**
     * Get an array with the names of all tables;
     *      
     * @return array
     * array with all names as string
     */
    public function get_table_names(){
        $sql = "SELECT name_id, `name`
                FROM view_dataTables;";
        $res_db = $this->db->query_db($sql);
        $res = array();
        foreach ($res_db as $key => $value) {
            $res[$value['name_id']] = $value['name'];
        }
        return json_encode($res);
    }

    /**
     * Get an array with the names of all tables; forms for dynamic and upload tables for statis
     * 
     * @param $data
     *  - `name` = table name
     * @retval array
     * array with all field names as string
     */
    public function get_table_fields($data){
        $form_id = $this->user_input->get_dataTable_id($data['name']);
        if (!$form_id) {
            // the form does not exist anymore
            return json_encode(array());
        }
        $res_db = $this->user_input->get_data($form_id, ' LIMIT 0, 1', false, -1, true, false);
        $res = array();
        if ($res_db) {
            foreach ($res_db as $key => $value) {
                array_push($res, $key);
            }
        }
        return json_encode($res);
    }

    /**
     * Get an array with the names of all groups
     * 
     * @return array
     * array with all names as string
     */
    public function get_groups()
    {
        $sql = "SELECT g.id, g.`name`
                FROM `groups` AS g
                INNER JOIN lookups l ON (g.id_group_types = l.id) 
                WHERE l.lookup_code = :group_type
                ORDER BY g.name";
        $res_db = $this->db->query_db($sql, array("group_type" => groupTypes_group));
        $res = array();
        foreach ($res_db as $key => $value) {
            array_push($res, $value['name']);
        }
        return json_encode($res);
    }

    /**
     * Get an array with all values for the requested loookup type
     * @param array $data with key "lookupType"
     * The requested lookup type
     * 
     * @retval array
     * array with all values for this lookup type
     */
    public function get_lookups($data)
    {
        if ($data) {
            $lookups = $this->db->fetch_table_as_select_values('lookups', 'lookup_code', array('lookup_value'), 'WHERE type_code=:tcode', array(":tcode" => $data['lookupType']));
            $res = [];
            foreach ($lookups as $key => $value) {
                $res[$value['value']] = $value['text'];
            }
            return json_encode($res);
        } else {
            return false;
        }
    }

    /**
     * Get an array with the names of all languages
     * 
     * @retval array
     * array with all languages
     */
    public function get_languages(){
        $res_db = $this->db->select_table('languages');
        $res = [];
        foreach ($res_db as $key => $value) {
            $res[$value['id']] = $value['language'];
        }
        return json_encode($res);
    }

    /**
     * Get an array with the file names in the assets table
     * @param object $data
     * With property 'filter', comma separated file extensions
     * @return array
     * array with all names as string
     */
    public function get_assets($data)
    {
        $res_db = $this->db->select_table('assets');
        $files = array();
        foreach ($res_db as $key => $value) {
            array_push($files, $value['file_name']);
        }        

        if ($data['filter'] != '') {
            $extensions = explode(",", $data['filter']); // Desired file extensions
            $filtered_files = array_filter($files, function ($file) use ($extensions) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                return in_array($extension, $extensions);
            });
            return json_encode(array_values($filtered_files));
        } else {
            return json_encode($files);
        }
    }
}
?>
