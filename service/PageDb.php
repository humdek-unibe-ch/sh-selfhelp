<?php
require_once __DIR__ . '/BaseDb.php';

/**
 * Class to handle the communication with the DB
 *
 * @author moiri
 */
class PageDb extends BaseDb
{
    /* Constructors ***********************************************************/

    /**
     * Open a connection to a mysql database
     *
     * @param string $server
     *  Address of the server.
     * @param string $database
     *  Name of the database.
     * @param string $login
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
     *
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
        $sql = "SELECT p.id, p.keyword, p.url, p.parent, a.name FROM pages AS p
            LEFT JOIN actions AS a ON p.id_actions = a.id
            WHERE p.intern = 0";
        return $this->query_db($sql);
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
        $page_info = array(
            "title" => "Unknown",
            "keyword" => $keyword,
            "url" => "",
            "id" => 0
        );
        $sql = "SELECT p.id, p.keyword, p.url
            FROM pages AS p WHERE keyword=:keyword";
        $info = $this->query_db_first($sql, array(":keyword" => $keyword));
        if($info)
        {
            $page_info["url"] = $info["url"];
            $page_info["id"] = intval($info["id"]);
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
        return $page_info;

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
        $sql = "SELECT ps.id_sections AS id, s.id_styles, s.name, s.owner
            FROM pages_sections AS ps
            LEFT JOIN pages AS p ON ps.id_pages = p.id
            LEFT JOIN sections AS s ON ps.id_sections = s.id
            WHERE p.keyword = :keyword
            ORDER BY ps.position, id";
        return $this->query_db($sql, array(":keyword" => $keyword));
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
        $sql = "SELECT f.name, pft.content
            FROM pages_fields_translation AS pft
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN pages AS p ON p.id = pft.id_pages
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
        $sql = "SELECT s.id, s.name, s.id_styles
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
     * @retval array
     *  The db result array where each entry has the following fields
     *   'name': the name of the section field
     *   'content': the content of the section field
     */
    public function fetch_section_fields($id)
    {
        $locale_cond = $this->get_locale_condition();
        $sql = "SELECT f.name, sft.content
            FROM sections_fields_translation AS sft
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            LEFT JOIN languages AS l ON l.id = sft.id_languages
            WHERE sft.id_sections = :id AND $locale_cond";

        return $this->query_db($sql, array(":id" => $id));
    }

    /**
     * Fetch the content of the style fields from the database given a style
     * name.
     *
     * @param int $id
     *  The id of the style.
     * @retval array
     *  The db result array where each entry has the following fields
     *   'name': the name of the section field
     *   'content': the content of the section field
     */
    public function fetch_style_fields($id)
    {
        $locale_cond = $this->get_locale_condition();
        $sql = "SELECT f.name, sft.content
            FROM styles_fields_translation AS sft
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            LEFT JOIN languages AS l ON l.id = sft.id_languages
            WHERE sft.id_styles = :id AND $locale_cond";

        return $this->query_db($sql, array(":id" => $id));
    }
}
?>
