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
    private $page_info;
    private $style_components;
    private $page_sections;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id_page
     *  The id of the page that is currently edited.
     */
    public function __construct($services, $id_page)
    {
        parent::__construct($services);
        $this->id_page = $id_page;
        $this->page_info = $this->db->fetch_page_info_by_id($this->id_page);
        $this->page_sections = $this->db->fetch_page_sections_by_id($id_page);
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
            $root_items[$root_idx]['children'][] = $this->add_item($id,
                $item['keyword'], array(),
                $this->router->generate("cms_show", array("id" => $id))
            );
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
            $res[] = $this->add_item($id, $item['keyword'], array(),
                $this->router->generate("cms_show", array("id" => $id))
            );
            if($item['url'] == "")
                $res[count($res)-1]['disable-root-link'] = true;
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
     *  An array with two keys 'page' and 'nav' where
     *   'page' holds a prepared array of displayable sections such that it can
     *   be passed to a list style.
     *   'nav' hodls a prepared array of navigation sections such that it can
     *   be passed to a list style.
     */
    private function prepare_section_list($items)
    {
        $res = array("page" => array(), "nav" => array());
        foreach($items as $item)
        {
            $id = intval($item['id']);
            if(intval($item['id_styles']) == NAVIGATION_STYLE_ID)
                $res["nav"][] = array(
                    "id" => $id,
                    "title" => $item['name'],
                    "children" => array(),
                    "url" => "#"
                );
            else
            {
                $children_db = $this->db->fetch_section_children($id);
                $children = $this->prepare_section_list($children_db);
                $res["page"][] = array(
                    "id" => $id,
                    "title" => $item['name'],
                    "children" => $children["page"],
                    "url" => "#"
                );
            }
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
        return new $componentClass($this->services);
    }

    /**
     * Fetch page data from the database and return a heirarchical array such
     * that it can be passed to a list style.
     *
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
     *  A prepared hierarchical array of global section items such that it can
     *  be passed to a list style.
     */
    public function get_global_sections()
    {
        $sql = "SELECT s.id, s.name, s.id_styles FROM sections AS s
            LEFT JOIN sections_hierarchy AS sh ON s.id = sh.child
            LEFT JOIN pages_sections AS ps ON s.id = ps.id_sections
            WHERE sh.child IS NULL AND ps.id_sections IS NULL";
        $sections_db = $this->db->query_db($sql);
        return $this->prepare_section_list($sections_db);
    }

    /**
     * Fetch section data from the database and return a heirarchical array such
     * that it can be passed to a list style.
     *
     * @retval array
     *  An array with two keys 'page' and 'nav' where
     *   'page' holds a prepared array of displayable sections such that it can
     *   be passed to a list style.
     *   'nav' hodls a prepared array of navigation sections such that it can
     *   be passed to a list style.
     */
    public function get_page_sections()
    {
        return $this->prepare_section_list($this->page_sections);
    }

    /**
     * Gets the currently active page id.
     *
     * @retval int
     *  The currently active page id.
     */
    public function get_active_page_id()
    {
        return $this->id_page;
    }
}
?>
