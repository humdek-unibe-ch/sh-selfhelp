<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the navigation component
 * such that the data can easily be displayed in the view of the component.
 */
class NavModel extends BaseModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
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
        $sql = "SELECT p.id, p.keyword, p.id_navigation_section, pft.content AS title
            FROM pages AS p
            LEFT JOIN pages_fields_translation AS pft ON pft.id_pages = p.id
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            WHERE p.parent = :parent AND $locale_cond AND f.name = 'label'
            AND p.nav_position IS NOT NULL
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
        $sql = "SELECT p.id, p.keyword, p.id_navigation_section, pft.content AS title
            FROM pages AS p
            LEFT JOIN pages_fields_translation AS pft ON pft.id_pages = p.id
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            WHERE p.nav_position IS NOT NULL AND $locale_cond AND f.name = 'label'
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
     *  A \<page array\> of the from
     *   \<keyword\> =>
     *      "title" => \<page_title\>
     *      "children" => \<page array\>
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
                    "id_navigation_section" => $item['id_navigation_section'],
                    "title" => $item['title'],
                    "children" => $children
                );
            }
        }
        return $pages;
    }

    /* Public Methods *********************************************************/

    /**
     * Fetch the first navigation section from a navigation page.
     *
     * @param int $id_parent
     *  The id of the parent navigation section.
     * @retval mixed
     *  If a child exists the id of the child is returned, null otherwise.
     */
    public function get_first_nav_section($id_parent)
    {
        if($id_parent === null)
            return;
        $sql = 'SELECT child FROM sections_navigation
            WHERE parent = :parent ORDER BY position';
        $id_child = $this->db->query_db_first($sql,
            array(':parent' => $id_parent));
        if($id_child && $id_child['child'])
            return intval($id_child['child']);
        return null;
    }

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
     * Return the number of new messages.
     *
     * @retval int
     *  The number of new messages.
     */
    public function get_new_message_count()
    {
        $sql = "SELECT count(c.id) AS count
            FROM chat AS c
            LEFT JOIN chatRoom_users AS cru ON cru.id_chatRoom = c.id_rcv_grp
            LEFT JOIN users AS u ON u.id = :uid
            LEFT JOIN users_groups AS ug ON ug.id_users = u.id
            WHERE (c.is_new = '1' AND c.id_snd != u.id)
                AND (cru.id_users = u.id OR cru.id_users IS NULL)
                AND (ug.id_groups != 3 OR c.id_rcv = u.id)";
        $res = $this->db->query_db_first($sql,
            array(":uid" => $_SESSION['id_user']));
        if($res)
            return intval($res['count']);
        else
            return 0;
    }

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

    /**
     * Checks whether a route exists.
     *
     * @param string $route
     *  The route to check.
     * @retval bool
     *  True if the route exists, false otherwise.
     */
    public function has_route($route) { return $this->router->has_route($route); }
}
?>
