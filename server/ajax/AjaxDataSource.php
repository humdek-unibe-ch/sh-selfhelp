<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseAjax.php";

/**
 * A small class to allow to fetch static or dynamic datat from the DB. This
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
     * Check wether a row of data passes the filter or not.
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
     * Read dynamic data form the database. This data is collected dynamically
     * through online forms from subjects.
     *
     * @param number $form_id
     *  The id of the form to fetch.
     * @param array $filters
     *  An assoziative array of filters. Refer to
     *  AjaxDataSource::check_filter_data() for more information.
     * @param boolean $single_user
     *  If true only fetch dynamic data from a single user, if false fetch
     *  dynamic data from all users.
     * @retval array
     *  Returns a list of assiciative arrays items. Each item corresponds to a
     *  data set collected from one form submission. The keys of each item
     *  correspond to the field names of the form.
     */
    private function fetch_data_table_dynamic($form_id, $filters, $single_user)
    {
        $res = array();
        $cond = "";
        $params = array( "id" => $form_id );
        if($single_user) {
            $cond = " AND user_id = :uid";
            $params["uid"] = $_SESSION['id_user'];
        }
        $sql = "SELECT * FROM view_user_input
            WHERE form_id = :id" . $cond . "
            ORDER BY
                CASE
                    WHEN record_id IS NULL
                    THEN edit_time
                    ELSE record_id
            END";
        $res_db = $this->db->query_db($sql, $params);
        if(!isset($res_db[0]['record_id']))
            return array();
        $last_row_id = $res_db[0]['record_id'];
        $item = array();
        foreach($res_db as $item_db) {
            if($item_db['record_id'] !== $last_row_id) {
                if($this->check_filter_data($filters, $item)) {
                    array_push($res, $item);
                }
                $item = array();
                $last_row_id = $item_db['record_id'];
            }
            $item[$item_db['field_name']] = $item_db['value'];
        }
        if($this->check_filter_data($filters, $item)) {
            array_push($res, $item);
        }
        return $res;
    }

    /**
     * Read static data from the database. This data is collected through a CSV
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
    private function fetch_data_table_static($table_id, $filters)
    {
        $res = array();
        $sql = "SELECT * FROM view_uploadTables
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
     * The search function which can be called by an AJAx call.
     *
     * @param $data
     *  The POST data of the ajax call:
     *   - 'name':        the name of the data to fetch.
     *   - 'single_user': flag to indicate whether to use dynamic data of a
     *                    single user or of all users
     * @retval array
     *  An array of user items where each item has the following keys:
     *   - 'value':     The email of the user.
     *   - 'id':        The id of the user.
     */
    public function get_data_table($data)
    {
        $sql = "SELECT * FROM view_data_tables WHERE table_name = :name";
        $source = $this->db->query_db_first($sql,
            array("name" => $data['name']));
        $filter = array();
        if(isset($_SESSION['data_filter'][$data['name']])
                && count($_SESSION['data_filter'][$data['name']]) > 0) {
                $filter = $_SESSION['data_filter'][$data['name']];
        }
        if($source['type'] === "static") {
            return $this->fetch_data_table_static($source['id'], $filter);
        } else if($source['type'] === "dynamic") {
            return $this->fetch_data_table_dynamic($source['id'], $filter,
                $data['single_user'] === "true");
        }
        return false;
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
}
?>
