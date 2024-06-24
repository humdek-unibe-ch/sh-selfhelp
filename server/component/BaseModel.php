<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * The class to define the basic functionality of a model.
 */
abstract class BaseModel
{
    /* Private Properties *****************************************************/

    /**
     *  The router instance is used to generate valid links.
     */
    protected $router;

    /**
     *  The db instance which grants access to the DB.
     */
    protected $db;

    /**
     * The instance to the navigation service which allows to switch between
     * sections, associated to a specific page.
     */
    protected $nav;

    /**
     * The login instance that allows to check user credentials.
     */
    protected $login;

    /**
     * The instnce of the access control layer (ACL) which allows to decide
     * which links to display.
     */
    protected $acl;

    /**
     * The parsedown instance that allows to parse markdown content.
     */
    protected $parsedown;

    /**
     * The instance instance that is used to log transactions in the database.
     */
    protected $transaction;

    /**
     * User input handler.
     */
    protected $user_input;

    /**
     * Mail handler.
     */
    protected $mail;

    /**
     * JobScheduler handler.
     */
    protected $job_scheduler;

    /**
     * An associative array holding the different available services. See the
     * class definition basepage for a list of all services.
     */
    protected $services;

    /**
     * The collection of child components that are assigend to this component.
     */
    protected $children;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        $this->children = array();
        $this->services = $services;
        $this->router = $services->get_router();
        $this->db = $services->get_db();
        $this->acl = $services->get_acl();
        $this->login = $services->get_login();
        $this->transaction = $services->get_transaction();
        $this->nav = $services->get_nav();
        $this->parsedown = $services->get_parsedown();
        $this->user_input = $services->get_user_input();
        $this->job_scheduler = $services->get_job_scheduler();
    }

    /** Private Methods *******************************************************/

    /**
     * Get the url of a navigation item, given an id.
     *
     * @param int $id
     *  The id of the navigation item to generate the url.
     * @retval string
     *  The generated url or the empty string if the url could not be generated.
     */
    private function get_nav_item_url($id)
    {
        if($this->nav == null) return "";
        if($id == 0) return "";
        return $this->get_link_url($this->nav->get_page_keyword(),
            array("nav" => $id));
    }

    /**
     * Set default settings for a curl call
     */
    static private function get_default_curl_settings($data)
    {
        $arr = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 100,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $data['header']
        );

        if (DEBUG) {
            //skip ssl checks for local testing
            $arr[CURLOPT_SSL_VERIFYHOST] = false;
            $arr[CURLOPT_SSL_VERIFYPEER] = false;
        }

        return $arr;
    }
    

    /* Protected Methods *********************************************************/

    /**
     * Fetch the data from the database base on the JSON configuration
     * @param array $data_config
     * Json configuration
     * @param int $user_id
     * Show the data for that user
     * @retval array
     * array with the retrieved fields and their values
     */
    protected function fetch_data($data_config, $user_id = null)
    {
        $result = array();
        try {
            foreach ($data_config as $key => $config) {
                // loop configs; DB requests
                $table_id = $this->user_input->get_dataTable_id($config['table']);                
                $data = null;
                if ($table_id) {
                    $filter = "ORDER BY record_id ASC";
                    if ($config['retrieve'] === 'last') {
                        $filter = "ORDER BY record_id DESC";
                    }
                    if (isset($config['filter']) && $config['filter'] != '') {
                        // if specific filter is used, overwrite it.
                        $filter = $config['filter'];
                    }
                    $current_user = true; //default value
                    if(isset($config['current_user'])){
                        // get the config value if it is set
                        $current_user = $config['current_user'];
                    }
                    $data = $this->user_input->get_data($table_id, $filter, $current_user, $user_id);
                    if ($data) {
                        $data = array_filter($data, function ($value) {
                            return (!isset($value["deleted"]) || $value["deleted"] != 1); // if deleted is not set, we retrieve data from internal/external form/table
                        });
                    }
                    if ($config['retrieve'] === 'JSON') {                        
                        // check if there are map fields defined, and if there are create the new fields with the selected values
                        if(isset($config['map_fields'])){
                            $map_fields = array();
                            foreach ($config['map_fields'] as $key => $value) {
                                $map_fields[$value['field_name']] = $value['field_new_name'];
                            }
                            if(count($map_fields) > 0){
                                foreach ($data as $d_key => $d_value) {
                                    foreach ($map_fields as $m_key => $m_value) {
                                        $data[$d_key][$m_value] = $d_value[$m_key];
                                    }
                                }
                            }
                        }
                        $display_table_name = $this->user_input->get_dataTable_displayName($table_id);
                        $result[(isset($config['scope']) && $config['scope'] != '' ? $config['scope'] . '.' : '') . $display_table_name] = $data; // if the scope is set, use the scope for naming
                    } else if (isset($config['all_fields']) && $config['all_fields'] && count($data) > 0) {
                        // return all fields
                        if ($config['retrieve'] === 'all' || $config['retrieve'] === 'all_as_array') {
                            $all_values = array();
                            foreach ($data as $key => $value) {
                                foreach ($value as $field_name => $field_value) {
                                    $var_name = (isset($config['scope']) && $config['scope'] != '' ? $config['scope'] . '.' : '') . $field_name;
                                    $all_values[$var_name][] = $field_value;
                                }
                            }
                            foreach ($all_values as $key => $value) {
                                if ($config['retrieve'] === 'all') {
                                    $all_values[$key] = implode(',', $value);
                                } else {
                                    $all_values[$key] = json_encode($value);
                                }
                            }
                            $result = array_merge($result, $all_values);
                        } else {
                            $scope = (isset($config['scope']) && $config['scope'] != '' ? $config['scope'] . '.' : '');
                            $prefix_arr = array_combine(
                                // Use array_map to apply the prefix to each key
                                array_map(function($key) use ($scope) {
                                    return $scope . $key;
                                }, array_keys($data[0])),
                                // The values remain the same
                                $data[0]
                            );
                            $result = array_merge($result, $prefix_arr);
                        }
                    } else if (isset($config['fields'])) {
                        // return only the selected fields
                        foreach ($config['fields'] as $key => $field) {
                            // loop fields
                            $i = 0;
                            $field_value = '';
                            $all_values = array();
                            foreach ($data as $key => $row) {
                                $val =  (isset($row[$field['field_name']]) && $row[$field['field_name']] != '') ? $row[$field['field_name']] : $field['not_found_text']; // get the first value                                
                                if ($config['retrieve'] != 'all' && $config['retrieve'] != 'all_as_array') {
                                    $field_value = $val;
                                    break; // we don need the others;
                                } else {
                                    $all_values[] = $val;
                                }
                                $i++;
                            }
                            if ($config['retrieve'] === 'all') {
                                $field_value = implode(',', $all_values);
                            } else if ($config['retrieve'] === 'all_as_array') {
                                $field_value = json_encode($all_values);
                            }
                            $var_name = (isset($config['scope']) && $config['scope'] != '' ? $config['scope'] . '.' : '') . $field['field_holder'];
                            $result[$var_name] = ($field_value == '' ? $field['not_found_text'] : $field_value);
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            return false;
        }
        return $result;
    }
    

    /* Public Methods *********************************************************/

    /**
     * Checks whether the current page is a CMS page.
     *
     * @retval bool
     *  true if the current page is a CMS page, false otherwise.
     */
    public function is_cms_page()
    {
        return ($this->is_link_active("cms")
            || $this->is_link_active("cmsSelect")
            || $this->is_link_active("cmsUpdate")
            || $this->is_link_active("cmsInsert")
            || $this->is_link_active("cmsDelete")
        );

    }

    /**
     * Checks whether the current page is a CMS page which is edited by the user.
     * Either in update, delete or insert mode
     *
     * @retval bool
     *  true if the current page is a CMS page, false otherwise.
     */
    public function is_cms_page_editing()
    {
        return ($this->is_link_active("cmsUpdate") ||
                $this->is_link_active("cmsInsert") ||
                $this->is_link_active("cmsDelete")
        );
    }

    /**
     * Generates the url of a link, given a router keyword.
     *
     * @param string $key
     *  A router key.
     * @param array $params
     *  The url parameters used to generate the url.
     *
     * @retval string
     *  The generated link url.
     */
    public function get_link_url($key, $params=array())
    {
        return $this->router->get_link_url($key, $params);
    }

    /**
     * Checks whether a link, defined by a router key, is currently active.
     *
     * @param string $key
     *  A router key.
     *
     * @retval bool
     *  True if the link specified bt the router key is active, false otherwise.
     */
    public function is_link_active($key)
    {
        return $this->router->is_active($key);
    }

    /**
     * Gets the child components.
     *
     * @retval array
     *  An array of style components.
     */
    public function get_children()
    {
        return $this->children;
    }

    /**
     * Set the child components.
     *
     * @param array $children
     *  An array of style components.
     */
    public function set_children($children)
    {
        $this->children = $children;
    }


    /**
     * Get the model services.
     *
     * @retval array
     *  An associative array with the available services.
     */
    public function get_services()
    {
        return $this->services;
    }

    /**
     * Return the url of the next navigation section if a navigation exists.
     *
     * @retval string
     *  The url of the next navigation section or the empty string if no
     *  navigation is avaliable.
     */
    public function get_next_nav_url()
    {
        return $this->get_nav_item_url($this->nav->get_next_id());
    }

    /**
     * Return the url of the previous navigation section if a navigation exists.
     *
     * @retval string
     *  The url of the previous navigation section or the empty string if no
     *  navigation is avaliable.
     */
    public function get_previous_nav_url()
    {
        return $this->get_nav_item_url($this->nav->get_previous_id());
    }

    /**
     * Gets the number of navigation items.
     *
     * @retval int
     *  The number of navigation items.
     */
    public function get_count()
    {
        if($this->nav != null)
            return $this->nav->get_count();
        return 0;
    }

    /**
     * Checks whether a navigation is available.
     *
     * @retval bool
     *  True if a navigation is available, false otherwise.
     */
    public function has_navigation()
    {
        return ($this->nav != null);
    }

    /**
     * Gets the hierarchical assembled navigation items.
     *
     * @return array
     *  A hierarchical array. See NavSectionModel::fetch_children($id_section).
     */
    public function get_navigation_items()
    {

        if($this->nav != null)
            return $this->nav->get_navigation_items();
        return array();
    }    

    /**
     * Get a list of languages and prepares the list such that it can be passed to a
     * list component.
     *
     * @retval array
     *  An array of items where each item has the following keys:
     *   'id':      The id of the language.
     *   'locale':   
     *   'language':   
     *   'csv_separator':
     */
    public function get_languages()
    {
        return $this->db->get_languages();
    }

    /**
     * get user groups from the database.
     *
     *  @retval array
     *  value int,
     *  text string
     */
    public function get_groups()
    {
        $groups = array();
        foreach ($this->db->select_table("`groups`") as $group) {
            array_push($groups, array("value" => intval($group['id']), "text" => $group['name']));
        }
        return $groups;
    }

    /**
     * Generate and return the url of a list item.
     *
     * @param int $pid
     *  The page id.
     * @param int $sid
     *  The root section id or the active section id if no root section is
     *  available.
     * @param int $ssid
     *  The active section id.
     * @return string
     *  The generated url.
     */
    public function get_cms_item_url($pid, $sid=null, $ssid=null)
    {
        if ($sid == $ssid) $ssid = null;
        if ($this->get_services()->get_user_input()->is_new_ui_enabled() && $this->is_link_active("cmsUpdate")) {
            return $this->router->generate("cmsUpdate", array("pid" => $pid, "sid" => $sid, "ssid" => $ssid, "mode" => UPDATE, "type" => "prop"));
        } else {
            return $this->router->generate(
                "cmsSelect",
                array("pid" => $pid, "sid" => $sid, "ssid" => $ssid)
            );
        }
    }

    /**
     * Execute curl calls
     * @param array $data
     * request_type, url, post_params
     * @return bool || object
     *  false or response
     */
    static public function execute_curl_call($data)
    {
        // curl module should be installed
        // sudo apt-get install php-curl
        try {
            $curl = curl_init();
            curl_setopt_array($curl, BaseModel::get_default_curl_settings($data));
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $data['request_type']);
            curl_setopt($curl, CURLOPT_URL, $data['URL']);
            if (isset($data['post_params'])) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data['post_params']);
            }

            $response = curl_exec($curl);
            $response = json_decode($response, true);

            curl_close($curl);
            return $response;
        } catch (Exception $e) {
            return false;
        }
    }    

    /**
     * Get the service class UserInput.
     *
     * @return object
     *  The UserInput service class.
     */
    public function get_user_input()
    {
        return $this->user_input;
    }

    /**
     * Get the service class DB.
     *
     * @return object
     *  The DB service class.
     */
    public function get_db()
    {
        return $this->db;
    }

    /**
     * Set the display name of an entry in the data tables.
     *
     * This method updates the `displayName` field of a row in the `dataTables` table
     * where the `name` field matches the zero-padded value of the provided `$id_sections`.
     *
     * @param int | string $name The name of the table. If it is a section id, we format it with leading zeros.
     * @param string $displayName The new display name to be set in the `dataTables` table.
     * @return bool|int Returns the result of the update operation. Typically, it returns the number of affected rows or false on failure.
     */
    public function set_dataTables_displayName($name, $displayName)
    {
        $res = $this->db->insert(
            'dataTables',
            array(
                "displayName" => $displayName,
                'name' => is_int($name) ? sprintf('%010d', $name) : $name
            ),
            array(
                "displayName" => $displayName
            )
        );
        $this->db->clear_cache($this->db->get_cache()::CACHE_TYPE_SECTIONS);
        return $res;
    }
    
}
?>
