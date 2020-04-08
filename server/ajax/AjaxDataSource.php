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
     * Read dynamic data form the database. This data is collected dynamically
     * through online forms from subjects.
     *
     * @param number $form_id
     *  The id of the form to fetch.
     * @param boolean $single_user
     *  If true only fetch dynamic data from a single user, if false fetch
     *  dynamic data from all users.
     * @retval array
     *  Returns a list of assiciative arrays items. Each item corresponds to a
     *  data set collected from one form submission. The keys of each item
     *  correspond to the field names of the form.
     */
    private function fetch_data_table_dynamic($form_id, $single_user)
    {
        if($single_user) {
            $sql = 'CALL get_form_data_for_user(' . $form_id . ', '
                . $_SESSION['id_user'] . ')';
            return $this->db->query_db($sql);
        } else {
            $sql = 'CALL get_form_data(' . $form_id . ')';
            return $this->db->query_db($sql);
        }
    }

    /**
     * Read static data from the database. This data is collected through a CSV
     * file upload.
     *
     * @param number $table_id
     *  The id of the uploaded CSV table.
     * @retval array
     *  Returns a list of assiciative arrays items. Each item corresponds to
     *  a row of the data table. The keys of each item correspond to the column
     *  names of the table.
     */
    private function _fetch_data_table_static($table_id, $filter)
    {
        $sql = 'CALL get_uploadTable_with_filter(:id, "'.$filter.'" )';
        return $this->db->query_db($sql, array(
            "id" => $table_id
        ));
    }
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
                if($this->check_data_table_static($filters, $item)) {
                    array_push($res, $item);
                }
                $item = array();
                $last_row_id = $item_db['row_id'];
            }
            $item[$item_db['col_name']] = $item_db['value'];
        }
        if($this->check_data_table_static($filters, $item)) {
            array_push($res, $item);
        }
        return $res;
    }

    private function check_data_table_static($filters, $item) {
        foreach($filters as $name => $filter) {
            $res_or = false;
            foreach($filter as $val) {
                if($item[$name] === $val) {
                    $res_or = true;
                    break;
                }
            }
            if($res_or === false) {
                /* return false; */
            }
        }
        return true;
    }

    /**
     *
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
     *
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
        $filter = "";
        if(isset($_SESSION['data_filter'][$data['name']])
                && count($_SESSION['data_filter'][$data['name']]) > 0) {
                /* $filter = "AND " . implode(" AND ", array_map(function($val) { */
                /*     return implode(" OR ", $val ); */
                /* }, $_SESSION['data_filter'][$data['name']])); */
                $filter = $_SESSION['data_filter'][$data['name']];
        }
        if($source['type'] === "static") {
            return $this->fetch_data_table_static($source['id'], $filter);
        } else if($source['type'] === "dynamic") {
            return $this->fetch_data_table_dynamic($source['id'], $data['single_user']);
        }
        return false;
    }

    /**
     *
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
     * Checks wheter the current user is authorised to perform AJAX requests.
     * This function overwrites the default access check and ignores the general
     * ACL settings for AJAX requests
     *
     * @retval boolean
     *  True if authorisation is granted, false otherwise.
     */
    public function has_access()
    {
        return $this->acl->has_access($_SESSION['id_user'],
            $_SESSION['current_page'], 'select');
    }
}
?>
