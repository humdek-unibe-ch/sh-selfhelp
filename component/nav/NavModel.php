<?php
/**
 * This class is used to prepare all data related to the navigation component
 * such that the data can easily be displayed in the view of the component.
 */
class NavModel
{
    /* Private Properties *****************************************************/

    private $db;
    private $acl;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param object $acl
     *  The instnce of the access control layer (ACL) which allows to decide
     *  which links to display.
     */
    public function __construct($db, $acl)
    {
        $this->db = $db;
        $this->acl = $acl;
    }

    /* Private Methods ********************************************************/

    /**
     * Fetches all children page links of a page from the database.
     *
     * @param int $id_parent
     *  The id of the parent page.
     * @retval array
     *  An array prepared by NavModel::prepare_pages.
     */
    private function fetch_children($id_parent)
    {
        $locale_cond = $this->db->get_locale_condition();
        $sql = "SELECT p.id, p.keyword, pft.content AS title FROM pages AS p
            LEFT JOIN pages_fields_translation AS pft ON pft.id_pages = p.id
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            WHERE p.parent = :parent AND $locale_cond AND f.name = 'label'
            AND p.nav_position > 0
            ORDER BY p.nav_position";
        $pages_db = $this->db->query_db($sql, array(":parent" => $id_parent));
        return $this->prepare_pages($pages_db);
    }

    /**
     * Fetches all root page links that are placed in the navbar from the
     * database.
     *
     * @retval array
     *  An array prepared by NavModel::prepare_pages.
     */
    private function fetch_pages()
    {
        $locale_cond = $this->db->get_locale_condition();
        $sql = "SELECT p.id, p.keyword, pft.content AS title FROM pages AS p
            LEFT JOIN pages_fields_translation AS pft ON pft.id_pages = p.id
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            WHERE p.nav_position > 0 AND $locale_cond AND f.name = 'label'
            AND p.parent IS NULL
            ORDER BY p.nav_position";
        $pages_db = $this->db->query_db($sql, array());
        return $this->prepare_pages($pages_db);
    }

    /**
     * Prepare a hierarchical array that contains the page links.
     * Note that only page links are returned with matching access rights.
     *
     * @param array $pages_db
     *  An associative array returned by a db querry.
     * @retval array
     *  A <page array> of the from
     *   <keyword> =>
     *      "title" => <page_title>
     *      "children" => <page array>
     *  where the keyword corresponds to the route identifier.
     */
    private function prepare_pages($pages_db)
    {
        $pages = array();
        foreach($pages_db as $item)
        {
            if($this->acl->has_access_select($_SESSION['id_user'], $item['id']))
            {
                $children = $this->fetch_children(intval($item['id']));
                $pages[$item['keyword']] = array(
                    "title" => $item['title'],
                    "children" => $children
                );
            }
        }
        return $pages;
    }

    /* Public Methods *********************************************************/

    /**
     * Fetches the name of the home page from the database.
     *
     * @retval string
     *  The name of the home page.
     */
    public function get_home() { return $this->db->get_link_title("home"); }

    /**
     * Fetches the name of the login page from the database.
     *
     * @retval string
     *  The name of the login page.
     */
    public function get_login() { return $this->db->get_link_title("login"); }

    /**
     * Fetches the name of the profile page from the database.
     *
     * @retval string
     *  The name of the profile page.
     */
    public function get_profile() {
        $keyword = "profile-link";
        $db_page = $this->db->fetch_page_info($keyword);
        $pages = $this->prepare_pages(array($db_page));
        if(array_key_exists($keyword, $pages))
            return $pages[$keyword];
        else
            return array();
    }

    /**
     * Fetches all page links that are placed in the navbar from the database.
     *
     * @retval array
     *  An array prepared by NavModel::prepare_pages.
     */
    public function get_pages() { return $this->fetch_pages(); }
}
?>
