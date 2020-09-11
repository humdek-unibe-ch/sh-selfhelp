<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";

/**
 * This class is used to prepare all data related to the grap style components
 * such that the data can easily be displayed in the view of the component.
 */
class MarkdownModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /** 
     * An array of get parameters.
     */
    private $params;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     * @param array $params
     *  An array of get parameters.
     */
    public function __construct($services, $id, $params)
    {
        parent::__construct($services, $id, $params);
        $this->params = $params;
    }

    /* Private Methods ********************************************************/

    /**
     * Fetch the data from the database base on the JSON configuration
     * @param array
     * Json configuration
     * @retval array 
     * array with the retrieved fields and their values
     */
    private function fetch_data($data_config)
    {
        $result = array();
        foreach ($data_config as $key => $config) {
            // loop configs; DB requests
            $table_id = $config['type'] === 'static' ? $this->get_static_table_id($config['table']) : $this->get_dynamic_table_id($config['table']);
            $data = null;
            if ($table_id) {
                if ($config['type'] === 'static') {
                    $filter = "ORDER BY row_id ASC";
                    if ($config['retrieve'] === 'last') {
                        $filter = "ORDER BY row_id DESC";
                    }
                } else {
                    $filter = "ORDER BY edit_time ASC";
                    if ($config['retrieve'] === 'last') {
                        $filter = "ORDER BY edit_time DESC";
                    }
                }
                $data = $config['type'] === 'static' ? $this->get_static_data($table_id, $filter) : $this->get_dynamic_data($table_id, $filter);
                foreach ($config['fields'] as $key => $field) {
                    // loop fields
                    $field_value =  isset($data[0][$field['field_name']]) ? $data[0][$field['field_name']] : $field['not_found_text']; // get the first value
                    if ($config['retrieve'] === 'all') {
                        // get the other values too
                        foreach ($data as $key => $row) {
                            if ($key > 0) {
                                // we already got the first row
                                if (isset($row[$field['field_name']])) {
                                    $field_value = $field_value . ';' . $row[$field['field_name']];
                                }
                            }
                        }
                    }
                    $result[$field['field_holder']] = $field_value;
                }
            }
        }
        return $result;
    }

    /**
     * Get staic data from the database
     * @param int $table_id
     * id of the table that we want to retrieve
     * @param string $filter
     * filter used to sort the data
     * @retval array
     * the results rows in array
     */
    private function get_static_data($table_id, $filter)
    {
        $sql = 'CALL get_uploadTable_with_filter(:table_id, :filter)';
        return $this->db->query_db($sql, array(
            ":table_id" => $table_id,
            ":filter" => $filter
        ));
    }

    /**
     * Get dynamic data from the database
     * @param int $table_id
     * id of the table that we want to retrieve
     * @param string $filter
     * filter used to sort the data
     * @retval array
     * the results rows in array
     */
    private function get_dynamic_data($table_id, $filter)
    {
        $sql = 'CALL get_form_data_for_user_with_filter(:table_id, :user_id, :filter)';
        return $this->db->query_db($sql, array(
            ":table_id" => $table_id,
            ":user_id" => $_SESSION['id_user'],
            ":filter" => $filter
        ));
    }

    /**
     * Get the static table id based on name
     * @param string $table_name
     * table name
     * @retval int table id
     */
    private function get_static_table_id($table_name)
    {
        $sql = 'SELECT * 
                FROM view_data_tables 
                WHERE table_name = :name';
        return $this->db->query_db_first($sql, array(
            ":name" => $table_name
        ))['id'];
    }

    /**
     * Get the dynamic table id based on name
     * @param string $table_name
     * table name
     * @retval int table id
     */
    private function get_dynamic_table_id($table_name)
    {
        $sql = 'SELECT * 
                FROM view_user_input 
                WHERE form_name = :name';
        return $this->db->query_db_first($sql, array(
            ":name" => $table_name
        ))['form_id'];
    }

    /**
     * Parse the page params and if they are needed in the config they are replaced with their values
     * @param array $data_config
     * The json config
     * @retval array the json parsed and parameters assigned
     */
    private function parse_params($data_config)
    {
        $str_data = json_encode($data_config);
        preg_match_all('~#\w+\b~', $str_data, $m);
        if ($m && !$this->params) {
            // params needed but not provided
            return false;
        }
        foreach ($m as $key => $value) {
            $param_name = str_replace('#', '', $value[0]);
            if (isset($this->params[$param_name])) {
                $ser_data = str_replace($value[0], $this->params[$param_name], $str_data);
            } else {
                // param is missing break
                return false;
            }
        }
        return json_decode($ser_data, true);
    }

    /* Protected Methods ******************************************************/



    /* Public Methods *********************************************************/

    /**
     * Retrieve the data based on the JSON configuration
     * @param array $data_config
     * JSON structure
     * @retval array with the retrieved fields and their values, empty string if fails.
     */
    public function retrieve_data($data_config)
    {
        $parsed_data = $this->parse_params($data_config);
        if (!$parsed_data) {
            return '';
        } else {
            return $this->fetch_data($parsed_data);
        }
    }
}
?>
