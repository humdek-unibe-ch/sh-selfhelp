<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the cms component such
 * that the data can easily be displayed in the view of the component.
 */
class CmsModel extends BaseModel
{
    /* Private Properties *****************************************************/

    private $id_page;
    private $id_section;
    private $page_info;
    private $style_components;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id_page
     *  The id of the page that is currently edited.
     * @param int $id_section
     *  The id of the section that is currently edited (only relevant for
     *  navigation pages).
     */
    public function __construct($services, $id_page, $id_section)
    {
        parent::__construct($services);
        $this->id_page = $id_page;
        $this->id_section = $id_section;
        $this->page_info = $this->db->fetch_page_info_by_id($id_page);
    }

    /* Private Methods ********************************************************/

    /**
     * Add a new list item.
     *
     * @param int $id
     *  The id of the list item.
     * @param string $title
     *  The stitle of the list item.
     * @param array $children
     *  The children of the list item which is an array of list items.
     * @param string $url
     *  The target url of the list item.
     */
    private function add_item($id, $title, $children, $url)
    {
        return array(
            "id" => $id,
            "title" => $title,
            "children" => $children,
            "url" => $url
        );
    }

    /**
     * Add children to the root page list
     *
     * @param array $root_items
     *  An array of prepared (i.e. put into a form such that they can be passed
     *  to a list style) root page items.
     * @param array $items
     *  The page items as they are teyrned from the db query.
     * @retval array
     *  A prepared hierarchical array such that it can be passed to a list
     *  style.
     */
    private function add_page_list_children($root_items, $items)
    {
        foreach($items as $item)
        {
            if($item['parent'] == "") continue;
            $id = intval($item["id"]);
            if(!$this->acl->has_access_select($_SESSION['id_user'], $id))
                continue;
            $root_idx = $this->get_item_index(intval($item['parent']),
                $root_items);
            $nav_sections = $this->get_navigation_sections($id);
            $count = array_push($root_items[$root_idx]['children'],
                $this->add_item($id, $item['keyword'], $nav_sections,
                    $this->router->generate("cms_show", array("id" => $id))));
            if(count($nav_sections) > 0)
                $root_items[$root_idx]['children'][$count-1]['disable-root-link'] = true;
        }
        return $root_items;
    }

    /**
     * Return the index of an itme, given its id.
     *
     * @param int $id
     *  The id of the item to be found.
     * @param array $items
     *  The array to search for the item with the id in question.
     * @retval int
     *  The index of the item with the goven id or -1 if the item was not found.
     */
    private function get_item_index($id, $items)
    {
        for($idx = 0; $idx < count($items); $idx++)
            if($items[$idx]['id'] == $id)
                return $idx;
        return -1;
    }

    /**
     * Fetch gloabl section data from the database and return a heirarchical
     * array such that it can be passed to a list style.
     *
     * @retval array
     *  A prepared hierarchical array of global section items such that it can
     *  be passed to a list style.
     */
    private function get_navigation_sections($id_page)
    {
        $sections_db = $this->db->fetch_page_navigation_sections_by_id($id_page);
        $res = array();
        foreach($sections_db as $item)
        {
            $id = intval($item['id']);
            $res[] = $this->add_item($id_page."-".$id, $item['name'], array(),
                $this->router->generate("cms_show_nav",
                    array("pid" => $id_page, "sid" => $id)));
        }
        return $res;
    }

    /**
     * Fetch the children of a section from the database and return a
     * heirarchical array such that it can be passed to a list style.
     *
     * @param int $id
     *  the id of the section to fetch the children
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style.
     */
    private function get_section_hierarchy($id)
    {
        $db_sections = $this->db->fetch_section_children($id);
        return $this->prepare_section_list($db_sections);
    }

    /**
     * Prepare an array with the root page items such that it can be passed to a
     * list style.
     *
     * @param array $items
     *  The page items as they are returned from the db query.
     * @retval array
     *  A prepared array such that it can be passed to a list style.
     */
    private function prepare_page_list_root($items)
    {
        $res = array();
        foreach($items as $item)
        {
            if($item['parent'] != "") continue;
            $id = intval($item["id"]);
            if(!$this->acl->has_access_select($_SESSION['id_user'], $id))
                continue;
            $nav_sections = $this->get_navigation_sections($id);
            $count = array_push($res,
                $this->add_item($id, $item['keyword'], $nav_sections,
                    $this->router->generate("cms_show", array("id" => $id))));
            if($item['url'] == "")
                $res[$count-1]['disable-root-link'] = true;
            if(count($nav_sections) > 0)
                $res[$count-1]['disable-root-link'] = true;
        }
        return $res;
    }

    /**
     * Prepare an array with section items such that it can be passed to a list
     * style.
     *
     * @param array $items
     *  The section items as they are returned from the db query.
     * @retval array
     */
    private function prepare_section_list($items)
    {
        $res = array();
        foreach($items as $item)
        {
            $id = intval($item['id']);
            $children_db = $this->db->fetch_section_children($id);
            $children = $this->prepare_section_list($children_db);
            $res[] = $this->add_item($id,
                $item['name'], $children, "");
        }
        return $res;
    }

    /**
     *
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style.
     */
    public function fetch_navigation_items($id)
    {
        $res = array();
        $sql = "SELECT sn.child AS id, s.name FROM sections_navigation AS sn
            LEFT JOIN sections AS s ON sn.child = s.id
            WHERE sn.parent = :id";
        $items_db = $this->db->query_db($sql, array(":id" => $id));
        foreach($items_db as $item_db)
        {
            $id_item = intval($item_db['id']);
            $children = $this->fetch_navigation_items($id_item);
            $res[] = $this->add_item($id_item, $item_db['name'], $children,
                $this->router->generate("cms_show_nav",
                    array("pid" => $this->id_page, "sid" => $id_item)));
        }
        return $res;
    }

    /* Public Methods *********************************************************/

    public function get_page_info()
    {
        return $this->page_info;
    }

    public function get_page_fields()
    {
        return $this->db->fetch_page_fields_by_id($this->id_page);
    }

    public function get_component()
    {
        $componentClass = ucfirst($this->page_info['keyword']) . "Component";
        return new $componentClass($this->services, $this->id_section);
    }

    /**
     * Fetch page data from the database and return a heirarchical array such
     * that it can be passed to a list style.
     *
     * @retval array
     *  A prepared hierarchical array of page items such that it can be passed
     *  to a list style.
     */
    public function get_pages()
    {
        $pages_db = $this->db->fetch_accessible_pages();
        $pages = $this->prepare_page_list_root($pages_db);
        return $this->add_page_list_children($pages, $pages_db);
    }

    /**
     * Fetch gloabl section data from the database and return a heirarchical
     * array such that it can be passed to a list style.
     *
     * @retval array
     *  A prepared hierarchical array of global section items such that it can
     *  be passed to a list style.
     */
    public function get_global_sections()
    {
        $sql = "SELECT s.id, s.name, s.id_styles FROM sections AS s
            LEFT JOIN sections_hierarchy AS sh ON s.id = sh.child
            LEFT JOIN pages_sections AS ps ON s.id = ps.id_sections
            LEFT JOIN pages_sections_navigation AS psn ON s.id = psn.id_sections
            LEFT JOIN sections_navigation AS sn ON s.id = sn.parent
            WHERE sh.child IS NULL AND ps.id_sections IS NULL
            AND psn.id_sections IS NULL AND sn.parent IS NULL";
        $sections_db = $this->db->query_db($sql);
        return $this->prepare_section_list($sections_db);
    }

    /**
     * Fetch section data from the database and return a heirarchical array such
     * that it can be passed to a list style.
     *
     * @retval array
     *  A prepared hierarchical array of global section items such that it can
     *  be passed to a list style.
     */
    public function get_page_sections()
    {
        $page_sections = $this->db->fetch_page_sections_by_id($this->id_page);
        $pages = $this->prepare_section_list($page_sections);
        if($this->page_info['id_navigation_section'] != null)
        {
            $sql = "SELECT name FROM sections WHERE id = :id";
            $section = $this->db->query_db_first($sql,
                array(":id" => $this->id_section));
            $pages[] = $this->add_item($this->id_section, $section['name'],
                $this->get_section_hierarchy($this->id_section), "");
        }
        return $pages;
    }

    /**
     *
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style.
     */
    public function get_navigation_hierarchy()
    {
        if($this->page_info['id_navigation_section'] != null)
            return $this->fetch_navigation_items(
                $this->page_info['id_navigation_section']);
        return array();
    }

    /**
     * Gets the currently active page id.
     *
     * @retval int
     *  The currently active page id.
     */
    public function get_active_page_id()
    {
        $id = $this->id_page;
        if($this->id_section != 0)
            $id .= "-" . $this->id_section;
        return $id;
    }

    /**
     * Gets the currently active page id.
     *
     * @retval int
     *  The currently active page id.
     */
    public function get_active_section_id()
    {
        return $this->id_section;
    }
}
?>
