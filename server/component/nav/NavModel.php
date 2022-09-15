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
                    "avatar" => $this->user_input->get_avatar($_SESSION['id_user']),
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
    public function get_pages() {
        $pages_db = $this->db->fetch_pages(-1, $_SESSION['language'], 'AND id_pageAccessTypes != (SELECT id FROM lookups WHERE lookup_code = "mobile")', 'ORDER BY nav_position');
        return $this->pages = $this->prepare_pages($pages_db);
    }

    /**
     * Fetches all page links that are placed in the navbar from the database foe mobile.
     *
     * @retval array
     *  An array prepared by NavModel::prepare_pages.
     */
    public function get_pages_mobile() { 
        $pages_db = $this->db->fetch_pages(-1, $_SESSION['language'], 'AND id_pageAccessTypes != (SELECT id FROM lookups WHERE lookup_code = "web")', 'ORDER BY nav_position');
        return $this->pages = $this->prepare_pages($pages_db);
     }

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
