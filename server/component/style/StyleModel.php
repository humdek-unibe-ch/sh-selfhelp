<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
require_once __DIR__ . "/StyleComponent.php";
require_once __DIR__ . "/BaseStyleComponent.php";
require_once __DIR__ . "/IStyleModel.php";
require_once __DIR__ . "/BaseStyleModel.php";
/**
 * This class is used to prepare all data related to the style component such
 * that the data can easily be displayed in the view of the component.
 */
class StyleModel extends BaseModel implements IStyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The ID of the section.
     */
    protected $section_id;

    /**
     * The name of the section.
     */
    protected $section_name;

    /**
     * The name of the style associated to the section.
     */
    protected $style_name;

    /**
     * The type of the style associated to the section.
     */
    protected $style_type;

    /**
     * The collection of fields that are attributed to this style component.
     */
    protected $db_fields;

    /** 
     * An array of get parameters.
     */
    protected $params;

    /**
     * The id of the parent page
     */    
    protected $id_page;  

    /**
     * If an entry record is passed from style entryVie to its children
     */
    protected $entry_record;

    /**
     * The result of the computeted condition
     */    
    protected $condition_result; 

    /**
     * The DB field data config
     */
    protected $data_config;

    /**
     * The parent id if it exists
     */
    protected $parent_id;

    /**
     * The relation if the component. Does it belong ot a page or a section, etc
     */
    protected $relation;

    /**
     * Keep data that can be printed out for debugging
     */
    protected $debug_data;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches a section item from the database and assignes
     * the fetched content to private class properties.
     *
     * @param object $services
     *  The service handler instance which holds all services
     * @param int $id
     *  The id of the database section item to be rendered.
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params=array(), $id_page=-1, $entry_record=array())
    {
        parent::__construct($services);
        $this->section_id = $id;
        $this->params = $params;
        $this->id_page = $id_page; 
        $this->entry_record = $entry_record;
        $this->style_name = $this->get_style_name_by_section_id($id);        
        if(isset($params['parent_id'])){
            $this->parent_id = $params['parent_id'];
        }
        if(isset($params['relation'])){
            $this->relation = $params['relation'];
        }
        if(!$this->is_cms_page())
        {
            $_SESSION['gender'] = $_SESSION['user_gender'];
            $_SESSION['language'] = $_SESSION['user_language'];
        }
        $this->db_fields['id'] = array(
            "content" => $id,
            "type" => "internal",
        );                                
        $fields = $this->db->fetch_section_fields($id);
        $this->set_db_fields($fields);   
        if(!$this->style_name){
            return;
        }         

        $this->calc_condition();

        if (($this->is_cms_page() || $this->condition_result['result'])) {
            $this->loadChildren();
        }
                
    }

    /* Private Methods ********************************************************/

    /** 
     * Calculate condition if exist and assign the value in the property condition_result;
    */
    private function calc_condition(){
        $condition = $this->get_db_field('condition', '');
        if ($condition != '') {
            if ($this->entry_record) {
                $condition = $this->get_entry_values($condition);
            }
        }        
        $this->condition_result = $this->services->get_condition()->compute_condition($condition, null, $this->get_db_field('id'));
    }    

    /**
     * Get the entry record;
     * @retval array;
     * The entry record;
     */
    private function calc_entry_record(){
        $record_id = isset($this->params['record_id']) ? intval($this->params['record_id']) : -1;
        if ($record_id > 0) {
            $formInfo = explode('-', $this->get_db_field("formName"));
            $form_id = $formInfo[0];
            if (isset($formInfo[1])) {
                $form_type = $formInfo[1];
            } else {
                return;
            }
            $own_entries_only =  $this->get_db_field("own_entries_only", 1);
            $filter = " AND deleted = 0 AND record_id = " . $record_id;
            $data = $this->user_input->get_data($form_id, $filter, $own_entries_only, $form_type);
            return $data && count($data) > 0 ? $data[0] : false; // return the first record
        } else {
            return;
        }
    }

    /**
     * Get entries values if there are any set
     * @param $condition
     * The condition value array
     * @retval array
     * Return the condition array
     */
    private function get_entry_values($condition){
        $condition = $this->get_entry_value($this->entry_record, json_encode($condition));
        return json_decode($condition, true);
    }

    /**
     * Get params starting with $ fot the entry output
     * @param string $input
     * The field value that contain params
     * @retval array 
     * Array with all params in the field value
     */
    private function get_entry_param($input)
    {        
        $res = [];
        if(!$input){
            return $res;    
        }
        if(is_array($input)){
            $input = json_encode($input);
        }
        preg_match_all('~\$\w+\b~', $input, $m);
        foreach ($m as $key => $value) {
            foreach ($value as $k => $param) {
                if ($param) {
                    $param_name = str_replace('$', '', $param);
                    $res[] = $param_name;
                }
            }
        }
        return $res;
    }

    /**
     * Load the children of the section
     */
    protected function loadChildren(){
        $db_children = $this->db->fetch_section_children($this->section_id);
        foreach($db_children as $child)
        {
            $this->params['parent_id'] = $child['parent_id'];
            $this->params['relation'] = $child['relation'];
            $this->children[$child['name']] = new StyleComponent($this->services, intval($child['id']), $this->params, $this->id_page, $this->entry_record);
        }
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
        foreach ($m as $key => $value) {
            if ($value) {
                $param_name = str_replace('#', '', $value[0]);
                if (isset($this->params[$param_name])) {
                    $ser_data = str_replace($value[0], $this->params[$param_name], $str_data);
                }
            }
        }
        return isset($ser_data) ? json_decode($ser_data, true) : $data_config;
    }
    
    /**
     * Check if there is dynamic data that should be calculated. If there are it is calculated and returned
     * @param object $field
     * The field which we are checking
     * @param object $data_config
     * The data config as json object
     * @param $user_name
     * the user_name
     * @param $user_code
     * the user_code
     * @return string
     * Return the field content
     */
    protected function calc_dynamic_values($field, $data_config, $user_name, $user_code){
        //adjust entry records 
        $this->debug_data['field'] = $field;
        $this->debug_data['data_config'] = $data_config;
        if ($this->entry_record) {
            //adjust entry value
            $field['content'] = $this->get_entry_value($this->entry_record, $field['content']);
        }
        // replace the field content with the global variables
        if ($field['content']) {
            $global_vars = array(
                '@user_code' => $user_code,
                '@project' => $_SESSION['project'],
                '@user' => $user_name,
                '__keyword__' => $this->router->get_keyword_from_url(),
                '__record_id__' => (isset($this->params['record_id']) ? intval($this->params['record_id']) : -1),
                '__platform__' => (isset($_POST['mobile']) && $_POST['mobile']) ? pageAccessTypes_mobile : pageAccessTypes_web
            );
            $this->debug_data['global_vars'] = $global_vars;
            if(strpos($field['content'], '__language__') !== false){
                $language = $this->db->get_user_language_id($_SESSION['id_user']);
                $global_vars['__language__'] = $language;
            }
            $field['content'] = $this->db->replace_calced_values($field['content'], $global_vars);
            $field['content'] = str_replace('@user_code', $user_code, $field['content']);
            $field['content'] = str_replace('@project', $_SESSION['project'], $field['content']);
            $field['content'] = str_replace('@user', $user_name, $field['content']);
            $global_values = $this->db->get_global_values(); 
            if($global_values){
                $field['content'] = $this->db->replace_calced_values($field['content'],  $global_values);
            }
            if ($data_config && $field['name'] != 'data_config') {
                // if there is data_config set and the field is not data_config, try to get dynamic data
                $fields = $this->retrieve_data($data_config);
                $field['content'] = $this->db->replace_calced_values($field['content'], $fields);
                if ($fields) {
                    foreach ($fields as $field_name => $field_value) {
                        if ($field_name[0] == '@') {
                            $field['content'] = str_replace($field_name, $field_value, $field['content']);
                        }
                    }
                }
                $this->debug_data['data_config_retrieved'] = $fields;
            }
        }      
        $this->debug_data['new_field_content'] = $field['content'];
        $this->debug_data['new_field_content_object'] = $field['content'] ? json_decode($field['content']) : false;
        return $field['content'];
    }

    /* Protected Methods ******************************************************/

    /**
     * Returns an url given a router keyword. The keyword \#back will generate
     * the url of the last visited page or the home page if the last visited
     * page is the current page or unknown. The keyword \#self points to the
     * current page.
     *
     * @retval string
     *  The generated url.
     */
    protected function get_url($url)
    {
        return $this->router->get_url($url);
    }

    /**
     * Set the content of a db_field attribute of the model.
     *
     * @param string $key
     *  The name of the db field.
     * @param mixed $content
     *  The content of the db field.
     */
    protected function set_db_field($key, $content)
    {
        if($this->get_db_field_full($key) == "") return;
        $this->db_fields[$key]['content'] = $content;
    }

    /**
     * Set the db_fields attribute of the model. Each field is assigned as an
     * key => value element where the key is the field name and the value the
     * field content.
     *
     * @param array $fields
     *  An array of field items where one item is an associative array of the
     *  form:
     *   "name" => name of the db field
     *   "content" => the content of the db field
     */
    protected function set_db_fields($fields)
    {
        $user_name = $this->db->fetch_user_name();
        $user_code = $this->db->get_user_code();
        $data_config_key = array_search('data_config', array_column($fields, 'name'));
        $data_config = $data_config_key ? $fields[$data_config_key]['content'] : null;
        if ($data_config) {
            if ($this->entry_record) {
                //adjust entry value
                $data_config = $this->get_entry_value($this->entry_record, $data_config);
            }
            // if data_config is set replace if there are any globals
            $global_vars = array(
                '@user_code' => $user_code,
                '@project' => $_SESSION['project'],
                '@user' => $user_name,
                '__keyword__' => $this->router->get_keyword_from_url(),
                '__record_id__' => (isset($this->params['record_id']) ? intval($this->params['record_id']) : -1),
                '__platform__' => (isset($_POST['mobile']) && $_POST['mobile']) ? pageAccessTypes_mobile : pageAccessTypes_web
            );
            if(strpos($data_config, '__language__') !== false){
                $language = $this->db->get_user_language_id($_SESSION['id_user']);
                $global_vars['__language__'] = $language;
            }
            $data_config = $this->db->replace_calced_values($data_config, $global_vars);
            
            $data_config = str_replace('@user_code', $user_code, $data_config);
            $data_config = str_replace('@project', $_SESSION['project'], $data_config);
            $data_config = str_replace('@user', $user_name, $data_config);
            $global_values = $this->db->get_global_values(); 
            if($global_values){
                $data_config = $this->db->replace_calced_values($data_config,  $global_values);
            }
            $data_config = json_decode($data_config, true);
        }
        foreach($fields as $field)
        {            

            // set style info
            $this->style_name = $field['style'];
            $this->style_type = $field['type'];
            $this->section_name = $field['section_name'];
            
            // load dynamic data if needed
            $field['content'] = $this->calc_dynamic_values($field, $data_config, $user_name, $user_code);

            $default = $field["default_value"] ?? "";
            if ($field['name'] == "url") {
                $field['content'] = $this->get_url($field['content']);
            } else if ($field['type'] == "markdown") {
                $field['content'] = $this->parsedown->text($field['content']);
            } else if ($field['type'] == "markdown-inline") {
                $field['content'] = $this->parsedown->line($field['content']);
            } else if ($field['type'] == "json") {
                $field['content']  = $field['content'] ? json_decode($field['content'], true) : array();
                /* $field['content'] = $this->json_style_parse($field['content']); */
            } else if ($field['type'] == "condition") {
                $field['content'] = $field['content'] ? json_decode($field['content'], true) : array();
            } else if ($field['type'] == "data-config") {
                $field['content'] = $field['content'] ? json_decode($field['content'], true) : array();
            } else if ($this->user_input->is_new_ui_enabled() && $this->is_link_active("cmsUpdate") && $field['name'] == "css") {
                // if it is the new UI and in edit mode remove the custom css for better visibility
                $field['content'] = '';
            }
            $this->db_fields[$field['name']] = array(
                "content" => $field['content'],
                "type" => $field['type'],
                "id" => $field['id'],
                "default" => $default,
            );
        }
    }    

    /* Public Methods *********************************************************/    

    /**
     * Returns the content of a data field given a specific key. If the key does
     * not exist an empty string is returned.
     *
     * @param string $key
     *  A database field name.
     * @param mixed $default
     *  The default field value to be returned if the field is not set.
     *
     * @retval string
     *  The content of the field specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field($key, $default="")
    {
        $field = $this->get_db_field_full($key);
        if (isset($field['content'])) {
            if ($field['content'] == "") {
                if (isset($field['default']) && $field['default'] != "")
                    return $field['default'];
                else
                    return $default;
            }
            return $field['content'];
        } else {
            return $default;
        }
    }

    /**
     * Returns the data field given a specific key. If the key does not exist,
     * an empty string is returned.
     *
     * @param string $key
     *  A database field name.
     *
     * @retval string
     *  The field specified by the key. An empty string if the
     *  key does not exist.
     */
    public function get_db_field_full($key)
    {
        if(array_key_exists($key, $this->db_fields))
            return $this->db_fields[$key];
        else
            return "";
    }

    /**
     * Returns the db field array where each field item is stores as a key,
     * value pair. The key corresponds to the name of the field and the value to
     * the content of the field.
     *
     * @retval array
     *  The key, value pairs describing data fields.
     */
    public function get_db_fields()
    {
        return $this->db_fields;
    }

    /**
     * Returns the style name. This will be used to load the corresponding
     * template.
     *
     * @retval string
     *  The style name.
     */
    public function get_style_name()
    {
        return $this->style_name;
    }

    /**
     * Returns the style type.
     *
     * @retval string
     *  The style type.
     */
    public function get_style_type()
    {
        return $this->style_type;
    }

    /**
     * Returns the section name.
     *
     * @retval string
     *  The section name.
     */
    public function get_section_name()
    {
        return $this->section_name;
    }

    /**
     * Search for a child section of a specific name.
     *
     * @param string $name
     *  The name of the section to be seacrhed
     * @retval reference
     *  Reference to the section instance.
     */
    public function &get_child_section_by_name($name)
    {
        if(array_key_exists($name, $this->children))
            return $this->children[$name];
        foreach($this->children as $child)
        {
            $section = $child->get_child_section_by_name($name);
            if($section !== null)
                return $section;
        }
        return null;
    }

    /**
     * This function is called after the style component is created via the
     * CMS. Redefine within the style.
     */
    public function cms_post_create_callback($model, $section_name,
        $section_style_id, $relation, $id) { }

    /**
     * This function is called after the style component is updated via the
     * CMS. Redefine within the style.
     */
    public function cms_post_update_callback($model, $data) { }

    /**
     * This function is called before the style component is updated via the
     * CMS. Redefine within the style.
     */
    public function cms_pre_update_callback($model, $data) { }

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

    /**
     * Get style name by id
     * @param int $style_id
     * The id of the style
     * @retval string
     * Return the name of the style
     */
    public function getStyleNameById($style_id)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_STYLES, $style_id, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $res = $this->db->query_db_first(
                'select name from styles where id = :id',
                array(":id" => $style_id)
            );
            $this->db->get_cache()->set($key, $res['name']);
            return $res['name'];
        }
    }

    /**
     * Getter function to get the entry record
     * @retval array 
     * the entry record
     */
    public function get_entry_record()
    {
        return $this->entry_record;
    }

    /**
     * Get the already computed condition result
     *
     * @retval array
     *  The result array
     */
    public function get_condition_result()
    {
        return $this->condition_result;
    }    

    /**
     * Getter, get the params
     * @retval array 
     * the params array
     */
    public function get_params(){
        return $this->params;
    }

    /**
     * Getter, get the id_page
     * @retval int 
     * Returns the id of the page
     */
    public function get_id_page(){
        return $this->id_page;
    }      

    /**
     * Get the value which is parsed with all params
     * @param array $entry_data
     * Array with the entry row
     * @param string value
     * The field value
     * @retval string
     * Return the value replaced with the params
     */
    public function get_entry_value($entry_data, $value)
    {
        if(!$value){
            return $value;
        }        
            
        $value = $this->db->replace_calced_values($value, $entry_data);
        $user_name = $this->db->fetch_user_name();
        $user_code = $this->db->get_user_code();
        $value = $this->db->replace_calced_values($value, array(
            '@user_code' => $user_code,
            '@project' => $_SESSION['project'],
            '@user' => $user_name
        ));
        return $value;
    }  

    /**
     * Check if the style can have children
     * @retval boolean
     * Return true if it can and false if it cannot
     */
    public function can_have_children(){
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_STYLES, $this->style_name, [__FUNCTION__]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return $get_result['result'];
        } else {
            $res = $this->db->query_db_first(
                "SELECT style_id
                FROM view_style_fields
                WHERE field_name = 'children' AND style_name = :name;",
                array(":name" => $this->style_name)
            );
            $this->db->get_cache()->set($key, array('result' =>isset($res['style_id'])));
            return isset($res['style_id']);
        } 
    }

    /**
     * Getter - get the parent id
     * @retval int 
     * the parent id
     */
    public function get_parent_id(){
        return $this->parent_id;
    }

    /**
     * Getter - get the relation
     * @retval string
     * Return the relation
     */
    public function get_relation(){
        return $this->relation;
    }

    /**
     * Get style name by section id
     * @param int $id
     * The section id
     * @return mixed
     * Return the style name or false
     */
    public function get_style_name_by_section_id($id)
    {
        $res = $this->db->fetch_section_info_by_id($id);
        if ($res && isset($res['style'])) {
            return $res['style'];
        } else {
            return false;
        }
    }

    /**
     * Get the debug data
     * @return object
     * Return the debug data
     */
    public function get_debug_data()
    {
        return $this->debug_data;
    }
    
}
?>

