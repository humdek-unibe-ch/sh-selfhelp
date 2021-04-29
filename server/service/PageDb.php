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
    /**
     * Caching page properties to reduce DB requests.
     */
    private $pages = array();

    /**
     * Caching page keyword ID maps to reduce DB requests.
     */
    private $page_keywords = array();

    /**
     * Caching page ID keyword maps to reduce DB requests.
     */
    private $page_ids = array();

    /**
     * Caching extended page properties to reduce DB requests.
     */
    private $pages_info = array();

    /* Constructors ***********************************************************/

    /**
     * Open a connection to a mysql database
     *
     * @param string $server
     *  Address of the server.
     * @param string $dbname
     *  Name of the database.
     * @param string $username
     *  The username of the database user.
     * @param string $password
     *  The password of the database user.
     */
    function __construct($server, $dbname, $username, $password ) {
        parent::__construct( $server, $dbname, $username, $password );
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
     * Get the locale condition to fetch the correct language.
     *
     * @retval string
     *  A valid mysql condition string.
     */
    public function get_locale_condition()
    {
        return "(l.locale = '".$_SESSION['language']."' OR l.locale = 'all')";
    }

    /**
     * Fetch all pages that are not internal.
     *
     * @retval array
     *  The db result array.
     */
    public function fetch_accessible_pages()
    {
        $sql = "SELECT p.id, p.keyword, p.url, p.parent, a.name AS action
            FROM pages AS p
            LEFT JOIN actions AS a ON p.id_actions = a.id
            WHERE p.id_type != :type
            ORDER BY p.keyword";
        return $this->query_db($sql, array('type' => INTERNAL_PAGE_ID));
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
        $sql = "SELECT id FROM fields WHERE name = :name";
        $res = $this->query_db_first($sql, array('name' => $name));
        if(!$res) return false;
        return $res['id'];
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
        if(!array_key_exists($keyword, $this->page_ids)) {
            $sql = "SELECT p.id FROM pages AS p WHERE keyword=:keyword";
            $id = $this->query_db_first($sql, array(":keyword" => $keyword));
            $this->page_ids[$keyword] = intval($id['id']);
        }
        return $this->page_ids[$keyword];
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
        if(!array_key_exists($id, $this->page_keywords)) {
            $sql = "SELECT p.keyword FROM pages AS p WHERE id=:id";
            $keyword = $this->query_db_first($sql, array(":id" => $id));
            $this->page_keywords[$id] = $keyword['keyword'];
        }
        return $this->page_keywords[$id];
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
        if(!array_key_exists($id, $this->pages)) {
            $sql = "SELECT p.* FROM pages AS p WHERE id=:id";
            $page = $this->query_db_first($sql, array(":id" => $id));
            $this->pages[$id] = $page;
        }
        return $this->pages[$id];
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
        $sql = "SELECT s.id, s.name, s.id_styles, st.name AS style
            FROM sections AS s
            LEFT JOIN styles AS st ON st.id = s.id_styles
            WHERE s.id = :id";
        return $this->query_db_first($sql, array(":id" => $id));
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
        if(array_key_exists($keyword, $this->pages_info))
            return $this->pages_info[$keyword];

        $page_info = array(
            "title" => "unknown",
            "keyword" => $keyword,
            "action" => "unknown",
            "access_level" => "select",
            "url" => "",
            "id" => 0,
            "id_navigation_section" => null,
            "parent" => null,
            "id_type" => 1,
            "protocol" => "",
            "is_headless" => false,
        );
        $sql = "SELECT p.id, p.keyword, p.url, p.id_navigation_section,
            p.protocol, a.name AS action, parent, is_headless, id_type, id_pageAccessTypes
            FROM pages AS p
            LEFT JOIN actions AS a ON a.id = p.id_actions
            WHERE keyword=:keyword";
        $info = $this->query_db_first($sql, array(":keyword" => $keyword));
        if($info)
        {
            $page_info["url"] = $info["url"];
            $page_info["id_type"] = intval($info["id_type"]);
            $page_info["id_pageAccessTypes"] = intval($info["id_pageAccessTypes"]);
            $page_info["parent"] = $info["parent"];
            $page_info["id"] = intval($info["id"]);
            $page_info["action"] = $info["action"];
            $page_info["protocol"] = $info["protocol"];
            $page_info["id_navigation_section"] = intval($info["id_navigation_section"]);
            $page_info["is_headless"] = ($info['is_headless'] == '1') ? true : false;
            $protocols = explode("|", $info["protocol"]);
            if(in_array("DELETE", $protocols)) $page_info["access_level"] = "delete";
            else if(in_array("PATCH", $protocols)) $page_info["access_level"] = "update";
            else if(in_array("PUT", $protocols)) $page_info["access_level"] = "insert";
            $locale_cond = $this->get_locale_condition();
            $sql = "SELECT pft.content AS title
                FROM pages_fields_translation AS pft
                LEFT JOIN languages AS l ON l.id = pft.id_languages
                LEFT JOIN fields AS f ON f.id = pft.id_fields
                WHERE pft.id_pages = :id AND $locale_cond AND f.name = 'label'";
            $info = $this->query_db_first($sql,
                array(":id" => $page_info["id"]));
            if($info)
                $page_info["title"] = $info["title"];
        }
        $this->pages_info[$keyword] = $page_info;
        return $page_info;
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
        $sql = "SELECT sn.child AS id, s.name, sn.position
            FROM sections_navigation AS sn
            LEFT JOIN sections AS s ON sn.child = s.id
            WHERE sn.parent = :id
            ORDER BY sn.position";
        return $this->query_db($sql, array(":id" => $id));
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
        $sql = "SELECT ps.id_sections AS id, s.id_styles, s.name, s.owner,
            ps.position
            FROM pages_sections AS ps
            LEFT JOIN pages AS p ON ps.id_pages = p.id
            LEFT JOIN sections AS s ON ps.id_sections = s.id
            WHERE p.keyword = :keyword
            ORDER BY ps.position, id";
        return $this->query_db($sql, array(":keyword" => $keyword));
    }

    /**
     * Fetch the content of the page fields from the database given a page id.
     *
     * @param int $id
     *  The page id.
     * @retval array
     *  The db result array.
     */
    public function fetch_page_fields_by_id($id)
    {
        $keyword = $this->fetch_page_keyword_by_id($id);
        return $this->fetch_page_fields($keyword);
    }

    /**
     * Fetch the content of the page fields from the database given a page
     * keyword.
     *
     * @param string $keyword
     *  The router keyword of the page.
     * @retval array
     *  The db result array where each entry has the following fields
     *   'name': the name of the page field
     *   'content': the content of the page field
     */
    public function fetch_page_fields($keyword)
    {
        $locale_cond = $this->get_locale_condition();
        $sql = "SELECT f.id AS id, f.name, pft.content, ft.name AS type
            FROM pages_fields_translation AS pft
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN pages AS p ON p.id = pft.id_pages
            LEFT JOIN fieldType AS ft ON ft.id = f.id_type
            WHERE p.keyword = :keyword AND $locale_cond";
        return $this->query_db($sql, array(":keyword" => $keyword));
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
        $sql = "SELECT s.id, s.name, s.id_styles, sh.position
            FROM sections_hierarchy AS sh
            LEFT JOIN sections AS s ON s.id = sh.child
            WHERE sh.parent = :id
            ORDER BY sh.position, s.id";
        return $this->query_db($sql, array(":id" => $id));
    }

    /**
     * Fetch the content of the section fields from the database given a section
     * id.
     *
     * @param int $id
     *  The id of the section.
     * @param in $gender
     *  The name of the gender of which the fields are fetched.
     * @retval array
     *  The db result array where each entry has the following fields
     *   'name': the name of the section field
     *   'content': the content of the section field
     */
    public function fetch_section_fields($id, $gender=null)
    {
        $user_name = $this->fetch_user_name();
        if($gender === null) $gender = $_SESSION['gender'];
        $locale_cond = $this->get_locale_condition();
        $sql = "SELECT f.id AS id, f.name, ft.name AS type, g.name AS gender,
            REPLACE(REPLACE(sft.content, '@user', :uname),
                '@project', :project) AS content, sf.default_value
            FROM sections_fields_translation AS sft
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            LEFT JOIN languages AS l ON l.id = sft.id_languages
            LEFT JOIN fieldType AS ft ON ft.id = f.id_type
            LEFT JOIN genders AS g ON g.id = sft.id_genders
            LEFT JOIN sections AS s ON s.id = sft.id_sections
            LEFT JOIN styles_fields AS sf ON sf.id_styles = s.id_styles
            AND sf.id_fields = f.id
            WHERE sft.id_sections = :id AND $locale_cond AND content != ''
            ORDER BY g.id DESC";

        $res_all = $this->query_db($sql, array(
            ":id" => $id,
            ":uname" => $user_name,
            ":project" => $_SESSION['project']
        ));
        $ids = array();
        $res = array();
        foreach($res_all as $item)
        {
            if($item['gender'] !== $gender && $item['gender'] !== "male")
                continue;
            if(in_array($item['id'], $ids))
                continue;
            $ids[] = $item['id'];
            $res[] = $item;
        }
        return $res;
    }

    /**
     * Get the name of the current user.
     *
     * @retval string
     *  The user name if set, otherwise the user email.
     */
    public function fetch_user_name()
    {
        $sql = "SELECT name, email FROM users WHERE id = :id";
        $res = $this->query_db_first($sql,
            array(":id" => $_SESSION['id_user']));
        if(!$res) return "unknown";
        if($res['name'] != "") return $res['name'];
        else return $res['email'];
    }

    /**
     * Get the avatar of the current user
     *
     * @param int $user_id
     * 
     * @retval string
     *  The avatar image of the current user or emty string.
     */
    public function get_avatar($user_id)
    {
        $sql_get_form_id = "SELECT form_id
                            FROM view_form
                            WHERE form_name = 'avatar';";
        $form = $this->query_db_first($sql_get_form_id);
        if ($form) {
            $sql = 'CALL get_form_data_for_user(:table_id, :user_id)';
            $avatar = $this->query_db_first($sql, array(
                ":table_id" => $form['form_id'],
                ":user_id" => $user_id
            ));
            return $avatar ? $avatar['avatar'] : '';
        } else {
            return '';
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
        $sql = "SELECT * FROM languages where id > 1;";
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
     * Fetch all modules from the databse
     *      
     *
     * @retval array
     * enabled; 0 = false; 1 = true 
     * module_name     
     * id
     */
    public function fetch_all_modules()
    {
        $sql = "SELECT * FROM modules;";
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
        $sql = "SELECT * FROM styles WHERE name = :name;";
        $res = $this->query_db_first($sql, array( ":name" => $name));
        if(!$res) return false;
        return $res['id'];
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

}
?>
