<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the navigation component
 * such that the data can easily be displayed in the view of the component.
 */
class NavModel extends BaseModel
{
    /**
     * The profile menu structure. This is kept seperate from the rest of the
     * menu because it will be rendered on the right.
     */
    private $profile = array("title" => "", "children" => array());

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
     * Fetches all root page links that are placed in the navbar from the
     * database.
     *
     * @retval array
     *  An array prepared by NavModel::prepare_pages.
     */
    public function fetch_pages()
    {
        $locale_cond = $this->db->get_locale_condition();
        $locale_cond2 = str_replace('l.','l_icon.',$this->db->get_locale_condition());
        $sql = "SELECT p.id, p.keyword, p.id_navigation_section,
            pft.content AS title, pft_icon.content AS icon, p.parent, p.nav_position, p.url
            FROM pages AS p
            LEFT JOIN pages_fields_translation AS pft ON pft.id_pages = p.id
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            LEFT JOIN pages_fields_translation AS pft_icon ON pft_icon.id_pages = p.id
            LEFT JOIN languages AS l_icon ON l_icon.id = pft_icon.id_languages
            LEFT JOIN fields AS f_icon ON f_icon.id = pft_icon.id_fields
            WHERE ($locale_cond AND f.name = 'title') AND ($locale_cond2 AND f_icon.name = 'icon') AND p.id_pageAccessTypes != 62
            ORDER BY p.nav_position";
        $pages_db = $this->db->query_db($sql, array());
        return $this->prepare_pages($pages_db);
    }

    /**
     * Fetches all root page links that are placed in the navbar from the
     * database. for mobile
     *
     * @retval array
     *  An array prepared by NavModel::prepare_pages.
     */
    public function fetch_pages_mobile()
    {
        $locale_cond = $this->db->get_locale_condition();
        $locale_cond2 = str_replace('l.','l_icon.',$this->db->get_locale_condition());
        $sql = "SELECT p.id, p.keyword, p.id_navigation_section,
            pft.content AS title, pft_icon.content AS icon, p.parent, p.nav_position, p.url
            FROM pages AS p
            LEFT JOIN pages_fields_translation AS pft ON pft.id_pages = p.id
            LEFT JOIN languages AS l ON l.id = pft.id_languages
            LEFT JOIN fields AS f ON f.id = pft.id_fields
            LEFT JOIN pages_fields_translation AS pft_icon ON pft_icon.id_pages = p.id
            LEFT JOIN languages AS l_icon ON l_icon.id = pft_icon.id_languages
            LEFT JOIN fields AS f_icon ON f_icon.id = pft_icon.id_fields
            WHERE ($locale_cond AND f.name = 'title') AND ($locale_cond2 AND f_icon.name = 'icon') AND p.id_pageAccessTypes != 63
            ORDER BY p.nav_position";
        $pages_db = $this->db->query_db($sql, array());
        return $this->prepare_pages($pages_db);
    }

    /**
     * Defines the structure of a single page navigation item.
     *
     * @param array $page
     *  An associative array of a single page entry returned by a db querry.
     * @retval array
     *  A \<page array\> with the keys
     *  - `title`: The title of the page
     *  - `keyword`: To the route identifier
     *  - `id_navigation_section`: The ID of a navigation section which allows
     *    to link the parent navigation page as a menu
     *  - `children`: \<page array\>
     */
    private function prepare_page($page)
    {
        return array(
            "id_navigation_section" => $page['id_navigation_section'],
            "title" => $page['title'],
            "keyword" => $page['keyword'],
            "is_active" => $page['is_active'],
            "url" => $page['url'],
            "icon" => $page['icon'],
            "children" => array()
        );
    }

    /**
     * Prepare a hierarchical array that contains the page links.
     * Note that only page links are returned with matching access rights.
     *
     * @param array $pages_db
     *  An associative array returned by a db querry.
     * @retval array
     *  A herarchical array of page items where ecah item is defined
     *  NavModel::prepare_page. The key of each page is the page id.
     */
    private function prepare_pages($pages_db)
    {
        $pages = array();
        foreach($pages_db as $key => $item) {
            $pages_db[$key]['acl'] = $this->acl->has_access_select(
                $_SESSION['id_user'], $item['id']);
            if($item['keyword'] === "profile-link") {
                $this->profile = array(
                    "id" => $item['id'],
                    "title" => $item["title"] .= ' (' . $this->db->fetch_user_name() . ')',
                    "keyword" => $item['keyword'],                    
                    "is_active" => false,
                    "children" => array()
                );
            }
            else if($item['parent'] === NULL
                    && $item['nav_position'] !== NULL
                    && $pages_db[$key]['acl']) {
                $item['is_active'] = false;
                if($this->is_link_active($item['keyword'])) {
                    $item['is_active'] = true;
                }
                $pages[$item['id']] = $this->prepare_page($item);
            }
        }

        foreach($pages_db as $item) {
            $item['is_active'] = false;
            if($this->is_link_active($item['keyword'])) {
                $item['is_active'] = true;
            }
            if($item['parent'] === $this->profile['id']) {
                $this->profile['children'][$item['id']] = $this->prepare_page($item);
                if($item['is_active']) {
                    $this->profile['is_active'] = true;
                }
            }
            else if($item['parent'] !== NULL
                    && $item['nav_position'] !== NULL
                    && $item['acl']
                    && array_key_exists($item['parent'], $pages)) {
                $pages[$item['parent']]['children'][$item['id']] = $this->prepare_page($item);
                if($item['is_active']) {
                    $pages[$item['parent']]['is_active'] = true;
                }
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
     * Checks whether the login page is currently active.
     *
     * @retval bool
     *  True if the login page is active, fale otherwise
     */
    public function get_login_active() { return $this->is_link_active("login"); }

    /**
     * Return the number of new messages.
     *
     * @retval int
     *  The number of new messages.
     */
    public function get_new_message_count()
    {
        $sql = "SELECT count(cr.id_chat) AS count FROM chatRecipiants AS cr
            WHERE cr.is_new = '1' AND cr.id_users = :uid";
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
    public function get_profile() { return $this->profile; }

    /**
     * Fetches all page links that are placed in the navbar from the database.
     *
     * @retval array
     *  An array prepared by NavModel::prepare_pages.
     */
    public function get_pages() { return $this->fetch_pages(); }

    /**
     * Fetches all page links that are placed in the navbar from the database foe mobile.
     *
     * @retval array
     *  An array prepared by NavModel::prepare_pages.
     */
    public function get_pages_mobile() { return $this->fetch_pages_mobile(); }

    /**
     * Checks whether a route exists.
     *
     * @param string $route
     *  The route to check.
     * @retval bool
     *  True if the route exists, false otherwise.
     */
    public function has_route($route) { return $this->router->has_route($route); }

    /**
     * Checks whether user has access to the chat. If not later the icon is not visualized
     *
     * @param string $key
     *  The page name of the chat; either "chatTherapist" or "chatSubject"
     * @retval bool
     *  True if the user has access to the chat.
     */
    public function has_access_to_chat($key){
        return $this->acl->has_access_select($_SESSION['id_user'], $this->db->fetch_page_id_by_keyword($key)); 
    }

    /**
     * Get the first group in which the user has chat permisions
     * @retval array
     * The group
     */
    public function get_chat_first_chat_group(){
        $sql = "SELECT ug.id_groups
                FROM users_groups ug
                INNER JOIN acl_groups acl ON (acl.id_groups = ug.id_groups)
                INNER JOIN pages p ON (acl.id_pages = p.id)
                WHERE id_users = :uid AND keyword = 'chatSubject' AND acl_select = 1 AND ug.id_groups > 2
                ORDER BY ug.id_groups ASC";
        return $this->db->query_db_first($sql, array(":uid"=>$_SESSION['id_user']));
    }
}
?>
