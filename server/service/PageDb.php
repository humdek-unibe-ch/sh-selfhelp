<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . '/BaseDb.php';

/**
 * Class to handle the communication with the DB
 *
 * @author moiri
 */
class PageDb extends BaseDb
{

    /* Private Properties *****************************************************/

    /**
     * The global values array
     */
    private $global_values;

    /* Constructors ***********************************************************/

    /**
     * Open a connection to a mysql database
     *
     * @param string $server
     *  Address of the server.
     * @param string $dbname
     *  Name of the database.
     * @param string $username
     *  The username of the database user.5
     * @param string $password
     *  The password of the database user.
     */
    function __construct($server, $dbname, $username, $password, $clockwork = null ) {
        parent::__construct( $server, $dbname, $username, $password, $clockwork );
        // $res = apcu_cache_info();
        $this->cache->clear_cache();
        // $this->cache->clear_cache($this->cache::CACHE_TYPE_PAGES, 80);
    }

    /* Public Methods *********************************************************/

    /**
     * Get the title of a page by providing a link keyword.
     *
     * @param string $keyword
     *  A link keyword, used to identify router paths.
     * @retval string
     *  Either the title of the page or the string "Unknown" if the title could
     *  not be found.
     */
    public function get_link_title($keyword)
    {
        $info = $this->fetch_page_info($keyword);
        return $info['title'];
    }

    /**
     * Fetch all pages that are not internal.
     *
     * @retval array
     *  The db result array.
     */
    public function fetch_accessible_pages()
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_PAGES, $this->cache::CACHE_ALL, [__FUNCTION__, INTERNAL_PAGE_ID]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT p.id, p.keyword, p.url, p.parent, l.`lookup_code` AS `action`, nav_position
            FROM pages AS p
            LEFT JOIN lookups AS l ON p.id_actions = l.id AND l.type_code = '" . pageActions . "'
            WHERE p.id_type != :type
            ORDER BY -nav_position desc, p.keyword";
            $res = $this->query_db($sql, array('type' => INTERNAL_PAGE_ID));
            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch the id of a field given the nam eof the field
     *
     * @param string $name
     * @retval mixed
     *  The id of the filed or false on failure
     */
    public function fetch_field_id_by_name($name)
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_FIELDS, $name, [__FUNCTION__]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT id FROM fields WHERE name = :name";
            $res = $this->query_db_first($sql, array('name' => $name));
            $this->cache->set($key, $res['id']);
            if (!$res) return false;
            return $res['id'];
        }
    }

    /**
     * Fetch the page id from the database, given a page keyword.
     *
     * @param int $keyword
     *  The page keyword.
     * @retval int
     *  The id of the page.
     */
    public function fetch_page_id_by_keyword($keyword)
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_PAGES, $keyword, [__FUNCTION__]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT p.id FROM pages AS p WHERE keyword=:keyword";
            $id = $this->query_db_first($sql, array(":keyword" => $keyword));
            $res = isset($id['id']) ? intval($id['id']) : $id;
            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch the page keyword from the database, given a page id.
     *
     * @param int $id
     *  The page id.
     * @retval string
     *  The keyword of the page.
     */
    public function fetch_page_keyword_by_id($id)
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_PAGES, $id, [__FUNCTION__]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT p.keyword FROM pages AS p WHERE id=:id";
            $keyword = $this->query_db_first($sql, array(":id" => $id));
            $res = isset($keyword['keyword']) ? $keyword['keyword'] : $keyword;
            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch the page  given a page id.
     *
     * @param int $id
     *  The page id.
     * @retval array
     *  The page columns.
     */
    public function fetch_page_by_id($id)
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_PAGES, $id, [__FUNCTION__]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT p.* FROM pages AS p WHERE id=:id";
            $page = $this->query_db_first($sql, array(":id" => $id));
            $this->cache->set($key, $page);
            return $page;
        }
    }

    /**
     * Fetch the main page information from the database, given a page id.
     *
     * @param int $id
     *  The page id.
     * @retval array
     *  The db result array.
     */
    public function fetch_page_info_by_id($id)
    {
        $keyword = $this->fetch_page_keyword_by_id($id);
        return $this->fetch_page_info($keyword);
    }

    /**
     * Fetch the main section information from the database, given a section id.
     *
     * @param int $id
     *  The section id.
     * @retval array
     *  The db result array.
     */
    public function fetch_section_info_by_id($id)
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_SECTIONS, $id, [__FUNCTION__]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT s.id, s.name, s.id_styles, st.name AS style
            FROM sections AS s
            LEFT JOIN styles AS st ON st.id = s.id_styles
            WHERE s.id = :id";
            $res = $this->query_db_first($sql, array(":id" => $id));
            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch the main page information from the database.
     *
     * @param string $keyword
     *  The keyword identifying the page.
     * @retval array
     *  The db result array.
     */
    public function fetch_page_info($keyword)
    {
        if (!$keyword) {
            return;
        }
        $page_id = $this->fetch_page_id_by_keyword($keyword);
        if ($page_id > 0) {
            $page_info = $this->fetch_pages($page_id, isset($_SESSION['language']) && $_SESSION['language'] != '' ? $_SESSION['language'] : LANGUAGE);
            if ($page_info && $page_info["protocol"]) {
                $protocols = explode("|", $page_info["protocol"]);
                if (in_array("DELETE", $protocols)) {
                    $page_info["access_level"] = "delete";
                } else if (in_array("PATCH", $protocols)) {
                    $page_info["access_level"] = "update";
                } else if (in_array("PUT", $protocols)) {
                    $page_info["access_level"] = "insert";
                }
            }
            return $page_info;
        } else {
            return;
        }
    }

    /**
     * Fetch all children of a section in the navigation hierarchy.
     *
     * @param int $id
     *  The section id.
     * @retval array
     *  The db result array.
     */
    public function fetch_nav_children($id)
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_SECTIONS, $id, [__FUNCTION__]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT sn.child AS id, s.name, sn.position
            FROM sections_navigation AS sn
            LEFT JOIN sections AS s ON sn.child = s.id
            WHERE sn.parent = :id
            ORDER BY sn.position";
            $res = $this->query_db($sql, array(":id" => $id));
            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch all section ids that are associated to a page, given a page id.
     *
     * @param int $id
     *  The page id.
     * @retval array
     *  The db result array.
     */
    public function fetch_page_sections_by_id($id)
    {
        $keyword = $this->fetch_page_keyword_by_id($id);
        return $this->fetch_page_sections($keyword);
    }

    /**
     * Fetch all section ids that are associated to a page.
     *
     * @param string $keyword
     *  The router keyword of the page.
     * @retval array
     *  The db result array where each entry has an 'id' field.
     */
    public function fetch_page_sections($keyword)
    {
        $page_id = $this->fetch_page_id_by_keyword($keyword); // convert the query to use page id  for the caching
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_PAGES, $page_id, [__FUNCTION__]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT ps.id_sections AS id, s.id_styles, s.name,
            ps.position, p.id AS parent_id, '" . RELATION_PAGE_CHILDREN . "' AS relation
            FROM pages_sections AS ps
            LEFT JOIN pages AS p ON ps.id_pages = p.id
            LEFT JOIN sections AS s ON ps.id_sections = s.id
            WHERE p.id = :page_id
            ORDER BY ps.position, id";
            $res =  $this->query_db($sql, array(":page_id" => $page_id));
            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Fetch all section ids that are associated to a parent section.
     *
     * @param int $id
     *  The id of the section.
     * @retval array
     *  The db result array where each entry has an 'id' field.
     */
    public function fetch_section_children($id)
    {    
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_SECTIONS, $id, [__FUNCTION__]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT s.id, s.name, s.id_styles, sh.position, sh.parent AS parent_id, '" . RELATION_SECTION_CHILDREN . "' AS relation
            FROM sections_hierarchy AS sh
            LEFT JOIN sections AS s ON s.id = sh.child
            WHERE sh.parent = :id
            ORDER BY sh.position, s.id";
            $res =  $this->query_db($sql, array(":id" => $id));
            $this->cache->set($key, $res);            
            return $res;
        }
    }

    /**
     * Fetch the content of the section fields from the database given a section
     * id.
     *
     * @param int $id
     *  The id of the section.
     * @retval array
     *  The db result array where each entry has the following fields
     *   'name': the name of the section field
     *   'content': the content of the section field
     */
    public function fetch_section_fields($id)
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_SECTIONS, $id, [__FUNCTION__, $_SESSION['language'], $_SESSION['gender']]);
        $get_result = $this->cache->get($key);
        $res = array();
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT 
                    f.id AS id, 
                    f.name, 
                    ft.name AS type,
                    COALESCE(
                        CASE
                            WHEN f.display = 0 THEN (
                                SELECT 
                                    COALESCE(
                                        (SELECT content FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = 1 AND sft.id_genders = 1 LIMIT 1),
                                        sf.default_value
                                    )
                            )
                            ELSE (
                                SELECT 
                                    COALESCE(
                                        (SELECT content FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = :id_language AND sft.id_genders = :gender AND content <> '' LIMIT 1),
                                        (SELECT content FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = :def_lang AND sft.id_genders = :gender AND content <> '' LIMIT 1),
                                        (SELECT content FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = :id_language AND sft.id_genders = :def_gender AND content <> '' LIMIT 1),
                                        (SELECT content FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = :def_lang AND sft.id_genders = :def_gender AND content <> '' LIMIT 1),
                                        ''
                                    )
                            )
                        END,
                        ''
                    ) AS content,
                    COALESCE(
                        CASE
                            WHEN f.display = 0 THEN (
                                SELECT 
                                    COALESCE(
                                        (SELECT meta FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = 1 AND sft.id_genders = 1 LIMIT 1),
                                        sf.default_value
                                    )
                            )
                            ELSE (
                                SELECT 
                                    COALESCE(
                                        (SELECT meta FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = :id_language AND sft.id_genders = :gender AND content <> '' LIMIT 1),
                                        (SELECT meta FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = :def_lang AND sft.id_genders = :gender AND content <> '' LIMIT 1),
                                        (SELECT meta FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = :id_language AND sft.id_genders = :def_gender AND content <> '' LIMIT 1),
                                        (SELECT meta FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = :def_lang AND sft.id_genders = :def_gender AND content <> '' LIMIT 1),
                                        ''
                                    )
                            )
                        END,
                        ''
                    ) AS meta,
                    sf.default_value, 
                    st.name AS style, 
                    s.name AS section_name, 
                    f.display, 
                    s.id as section_id
                FROM 
                    sections AS s 
                LEFT JOIN 
                    styles_fields AS sf ON sf.id_styles = s.id_styles
                LEFT JOIN 
                    fields AS f ON f.id = sf.id_fields
                LEFT JOIN 
                    fieldType AS ft ON ft.id = f.id_type
                LEFT JOIN 
                    styles AS st ON st.id = s.id_styles
                LEFT JOIN 
                    lookups AS lt ON lt.id = st.id_type AND lt.type_code = 'styleType'
                WHERE 
                    s.id = :id;";

            $res = $this->query_db($sql, array(
                ":id" => $id,
                ":id_language" => $_SESSION['language'],
                ":gender" => $_SESSION['gender'],
                ":def_lang" => LANGUAGE,
                ":def_gender" => MALE_GENDER_ID
            ));

            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Get the name of the current user.
     *
     * @retval string
     *  The user name if set, otherwise the user email.
     */
    public function fetch_user_name()
    {
        if (isset($_SESSION['user_name']) && $_SESSION['user_name'] != '') {
            return $_SESSION['user_name'];
        }
        $sql = "SELECT `name`, email FROM users WHERE id = :id";
        $res = $this->query_db_first($sql, array(":id" => $_SESSION['id_user']));
        if ($res && (isset($res['name']) || isset($res['email']))) {
            $_SESSION['user_name'] = isset($res['name']) ? $res['name'] : '';
            return $_SESSION['user_name'];
        } else {
            return "unknown";
        }
    }    

    /**
     * Get the user email of the current user.
     *
     * @retval string
     *  The user  email.
     */
    public function fetch_user_email()
    {
        if (isset($_SESSION['user_email']) && $_SESSION['user_email'] != ''&& $_SESSION['user_email'] != 'guest') {
            return $_SESSION['user_email'];
        }
        $sql = "SELECT email FROM users WHERE id = :id";
        $res = $this->query_db_first($sql, array(":id" => $_SESSION['id_user']));
        if ($res && isset($res['email'])) {
            $_SESSION['user_email'] = isset($res['email']) ? $res['email'] : '';
            return $_SESSION['user_email'];
        } else {
            return "unknown";
        }
    }  

    /**
     * Fetch the list of languages
     *
     * @retval array
     *  A list of db items where each item has the keys
     *   'id':      The id of the language.
     *   'locale':   
     *   'language':   
     *   'csv_separator':
     */
    public function fetch_languages()
    {
        $sql = "SELECT 1*id as id, locale, language, csv_separator FROM languages where id > 1;";
        return $this->query_db($sql);
    }

    /**
     * Fetch the list of genders
     *
     * @retval array
     *  A list of db items where each item has the keys
     *   'id':      The id of the gender.
     *   'name':   
     */
    public function fetch_genders()
    {
        $sql = "SELECT 1*id as id, name FROM genders";
        return $this->query_db($sql);
    }

    /**
     * Fetch cmsPreferences
     *
     * @retval array
     *  All preferences     
     *   'callback_api_key':   
     *   'default_lanhuage_id':   
     *   'default_lanhuage':
     */
    public function fetch_cmsPreferences()
    {
        $sql = "SELECT * FROM view_cmsPreferences;";
        return $this->query_db($sql);
    }

    /**
     * Fetch the id of a style given the name of the style
     *
     * @param string $name
     *  The name of the style.
     * @retval mixed
     *  The id of the style or false on failure
     */
    public function fetch_style_id_by_name($name)
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_STYLES, $name, [__FUNCTION__]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT * FROM styles WHERE name = :name;";
            $res = $this->query_db_first($sql, array(":name" => $name));
            $this->cache->set($key, $res);
            if (!$res) return false;
            return $res['id'];
        }
    }

    /**
     * Get values from table and retrun them in array text values for select options
     * Example call fetch_table_as_select_values('groups', 'id', array('name'),'WHERE id=:gid', array(":gid"=>3))
     * @param string $table_name
     * the name of the table that we want to fetch
     * @param string $value_column 
     * the name of the column which will be the value
     * @param array $text_columns_array
     * array with the columns that we want in the text; they are separated with ` - `
     * @param string $where_clause
     * where clause if we want to have some filtering
     * @param array $arguments
     * the arguments of the parameters in the wehere cluase
     * @retval array
     *  The array which can be used in the select style as items
     */
    public function fetch_table_as_select_values($table_name, $value_column, $text_columns_array, $where_clause = '', $arguments = array())
    {
        $sql = "SELECT " . $value_column . ',' . implode(',', $text_columns_array) . ' FROM ' . $table_name . ' ' . $where_clause;
        $res = $this->query_db($sql, $arguments);
        $arr = array();
        foreach ($res as $val) {
            $text = '';
            foreach ($text_columns_array as $key => $column) {
                if($text == ''){
                    $text = $text . $val[$column];
                }else{
                    $text = $text . ' - ' . $val[$column];
                }
            }
            array_push($arr, array("value" => $val[$value_column], "text" => $text));
        }
        return $arr;
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
        $res = array();
        foreach ($this->fetch_languages() as $language) {
            $res[] = array(
                "id" => $language["id"],
                "locale" => $language["locale"],
                "title" => $language["language"]                
            );
        }
        return $res;
    }

    /**
     * Getuser code
     * @retval string
     * return the user code if it is set
     */
    public function get_user_code()
    {
        if(isset($_SESSION['user_code'])){
            return $_SESSION['user_code'];
        }
        if ($_SESSION['id_user'] == 1){
            // no logged user, no code
            return false;
        }
        $res = $this->query_db_first('SELECT code
                                        FROM view_user_codes
                                        WHERE id = :id', array(':id' => $_SESSION['id_user']));
        if ($res && isset($res['code'])) {
            $_SESSION['user_code'] = $res['code'];
            return $res['code'];
        } else {
            return false;
        }
    }

    /**
     * Get pages from table pages
     * @param int $page_id
     * The page id, if it is -1 returns all pages
     * @param int $language_id
     * the language that we need page fields translated
     * @param string $filter 
     * sql filter if there are some
     * @param string $order_by 
     * sql ordering if there is some
     * @retval array
     * array with the returned page or pages
     */
    public function fetch_pages($page_id, $language_id, $filter = '', $order_by = '')
    {
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_PAGES, $page_id, [__FUNCTION__, $language_id, $this->get_default_language(), $filter, $order_by]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {    
            $sql = 'CALL get_page_fields(:page_id, :language_id, :default_language_id, :filter, :order_by)';
            $params = array(
                ":page_id" => $page_id,
                ":language_id" => $language_id,
                ":default_language_id" => $this->get_default_language(),
                ":filter" => $filter,
                ":order_by" => $order_by
            );
            if ($page_id == -1) {
                // return all
                $res = $this->query_db($sql, $params);
            } else {
                // return the page as single
                $res = $this->query_db_first($sql, $params);
            }
            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Get the info for the selected section
     * @param int $id
     * the section id
     * @retval object with the section info from DB
     */
    public function get_style_component_info($id)
    {        
        $key = $this->cache->generate_key($this->cache::CACHE_TYPE_SECTIONS, $id, [__FUNCTION__]);
        $get_result = $this->cache->get($key);
        if ($get_result !== false) {
            return $get_result;
        } else {
            $sql = "SELECT s.name, lt.lookup_code AS type
            FROM styles AS s
            LEFT JOIN lookups AS lt ON lt.id = s.id_type AND lt.type_code = 'styleType'
            LEFT JOIN sections AS sec ON sec.id_styles = s.id
            WHERE sec.id = :id";
            $res = $this->query_db_first($sql, array(":id" => $id));
            $this->cache->set($key, $res);
            return $res;
        }
    }

    /**
     * Return the default language if it is set in the preferences, otherwise set the default in config.
     */
    public function get_default_language()
    {
        if (isset($_SESSION['default_language_id'])) {
            return $_SESSION['default_language_id'];
        }
        $pref = $this->fetch_cmsPreferences();
        $default_language_id = !empty($pref) && $pref[0]['default_language_id'] ? $pref[0]['default_language_id'] : LANGUAGE;
        $_SESSION['default_language_id'] = $default_language_id;
        return $_SESSION['default_language_id'];
    }

    /**
     * Clear the cache, if no parameter is given it will clear all the cache. If parameters are given it will clear the cache based on their values
     * @param string $type = null
     * The type od the stored data - the types are defined as constants in the Cache class
     * @param int $id = null
     * the id of the object
     */
    public function clear_cache($type = null, $id = null)
    {
        $this->cache->clear_cache($type, $id);
    }

    /**
     * Get selfhelp translations as array
     * @retval array
     * return translations array for the selected language
     */
    public function get_global_values()
    {
        if (!isset($this->global_values)) {
            // check the database only once. If it is already assigned do not make a query and just returned the already assigned value
            $global_values_page = $this->fetch_page_info(SH_GLOBAL_VALUES);
            if(isset($global_values_page[PF_GLOBAL_VALUES])){
                try {
                    $this->global_values = json_decode($global_values_page[PF_GLOBAL_VALUES], true);
                } catch (\Throwable $th) {
                    $this->global_values = array();
                }
            }else{
                $this->global_values = array();
            }
        }
        if(!$this->global_values){
            $this->global_values = array();
        }
        return $this->global_values;
    }

    /**
     * Replace the calculated values
     * @param string or array $field_content
     * The value of the field contetn
     * @param array $calc_formula_values
     * the calculated variables with their value
     * @return string or array
     * Return the modified value of the field_content
     */
    public function replace_calced_values($field_content, $calc_formula_values)
    {
        $is_array = false;
        if (is_array($field_content)) {
            $is_array = true;
            $field_content = json_encode($field_content);
        }
        $field_content = preg_replace_callback('~{{({{)?(.*?)(}})?}}~s', function ($m) use ($calc_formula_values) {
            // Extracting the variable name
            $res = trim(isset($m[2]) ? $m[2] : '');

            // Check if the variable name is not empty
            if (!empty($res)) {
                // Check if the variable exists in the $calc_formula_values array
                if (isset($calc_formula_values[$res])) {
                    // Check if the match has exactly four curly braces
                    if (isset($m[1]) && isset($m[3])) {
                        // Return the value of the variable enclosed with double curly braces
                        return '{{' . addslashes($calc_formula_values[$res]) . '}}';
                    } else {
                        // Return the original pattern if the match doesn't have exactly four curly braces
                        return $m[0];
                    }
                } else {
                    // Return the original pattern if the variable is not found
                    return $m[0];
                }
            } else {
                // Return empty string if no variable name found
                return '';
            }
        }, $field_content);
        try {
            foreach ($calc_formula_values as $var => $var_value) {
                if (is_array($var_value)) {
                    $field_content = preg_replace('#\{\{' . $var . '\}\}#s', addslashes(json_encode($var_value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), $field_content);
                } else if ($var) {
                    if ($var_value !== null) {
                        $var_value = preg_replace('/\r|\n/', '\n', trim($var_value));
                    }
                    // if (json_decode($field_content)) {
                    //     // if the variable is array then do not add slashes
                    //     $var_value = str_replace('"', "'", $var_value); // all " should be only single in JSON
                    //     $field_content = preg_replace('#\{\{' . $var . '\}\}#', $var_value, $field_content);
                    // } else {
                    $field_content = preg_replace('#\{\{' . preg_quote($var, '#') . '\}\}#s', addslashes($var_value ?? ''), $field_content);
                    // }                    
                }
            }
        } catch (\Throwable $th) {
            return $field_content;
        }
        return $is_array ? json_decode($field_content, true) : $field_content;
    }

    /**
     * Get the user selected language
     * @param $id_users
     * The id of the user
     * @return int
     * Return the saved language id or false if not found
     */
    public function get_user_language_id($id_users){
        $sql = "SELECT id_languages
                FROM users u                
                WHERE u.id = :uid";
        $res = $this->query_db_first($sql, array(
            ':uid' => $id_users
        ));
        return isset($res['id_languages']) ? $res['id_languages'] : false;
    }

    /**
     * Get the user last login date
     * @param $id_users
     * The id of the user
     * @return string
     * Return the last user login date
     */
    public function get_user_last_login_date($id_users){
        $sql = "SELECT COALESCE(last_login, 'never') AS last_login
                FROM users u                
                WHERE u.id = :uid";
        $res = $this->query_db_first($sql, array(
            ':uid' => $id_users
        ));
        return isset($res['last_login']) ? $res['last_login'] : false;
    }

    /**
     * Check if the settings are for anonymous_users
     * @return bool
     * Return the result
     */
    public function is_anonymous_users(){
        return $this->fetch_cmsPreferences()[0]['anonymous_users'];
    }

    /**
     * Retrieves the current Git version tag from the specified directory.
     *
     * @param string $dir The directory containing the Git repository.
     * @param string $git_command The command to execute to retrieve the Git version tag.
     * @return string|null The Git version tag if available, or null if not found.
     */
    public function get_git_version($dir, $git_command = 'git describe --tags')
    {
        if ($git_command == 'git describe --tags') {
            //change dir only here
            chdir($dir);
        }
        $version = shell_exec($git_command);
        if ($version) {
            $version = rtrim($version);
        }
        return $version ? $version : "unknown";
    }

    /**
    * Retrieves the user ID based on the provided user code.
    *
    * This method queries the database to find the user ID associated with the given
    * user code from the `view_user_codes` view. If a matching user code is found,
    * the corresponding user ID is returned. If no match is found, it returns `false`.
    *
    * @param string $user_code The unique user code to search for in the database.
    *
    * @return int|false Returns the user ID as an integer if found, or `false` if no match is found.
    */
    public function get_user_id($user_code)
    {
        $res = $this->query_db_first('SELECT id
                                        FROM view_user_codes
                                        WHERE code = :user_code', array(':user_code' => $user_code));
        if ($res && isset($res['id'])) {
            return $res['id'];
        } else {
            return false;
        }
    }

    /**
     * Retrieves global variables for use within the application.
     * @return array An array containing global variables such as user code, project, user name, keywords, and platform.
     */
    public function get_global_vars()
    {
        $user_name = $this->fetch_user_name();
        $user_code = $this->get_user_code();
        $user_email = $this->fetch_user_email();
        $global_vars = array(
                '@user_code' => $user_code,
                '@id_users' => $_SESSION['id_user'],
                '@project' => $_SESSION['project'],
                '@user' => $user_name,
                '@user_email' => $user_email,
                '__platform__' => (isset($_POST['mobile']) && $_POST['mobile']) ? pageAccessTypes_mobile : pageAccessTypes_web
            );
        return $global_vars;
    } 

}
?>
