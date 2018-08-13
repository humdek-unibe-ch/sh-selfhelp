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
    private $mode;
    private $id_section;
    private $id_root_section;
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
     * @param int $id_root_section
     *  The root id of a page or the section that is currently selected.
     * @param int $id_section
     *  The id of the section that is currently selected (only relevant for
     *  navigation pages).
     * @param string $mode
     *  The mode of the page: 'select', 'update', 'insert', or 'delete'
     */
    public function __construct($services, $id_page, $id_root_section,
        $id_section, $mode)
    {
        parent::__construct($services);
        $this->mode = $mode;
        $this->id_page = $id_page;
        $this->id_section = $id_section;
        $this->id_root_section = $id_root_section;
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
            if(!$this->has_access($this->mode, $id))
                continue;
            $root_idx = $this->get_item_index(intval($item['parent']),
                $root_items);
            array_push($root_items[$root_idx]['children'],
                $this->add_item($id, $item['keyword'], array(),
                    $this->get_item_url($id)));
        }
        return $root_items;
    }

    /**
     * Generate and return the url of a list item.
     *
     * @param int $pid
     *  The page id.
     * @param int $sid
     *  The root section id or the active section id if no root section is
     *  available.
     * @param int $ssid
     *  The active section id.
     * @return string
     *  The generated url.
     */
    private function get_item_url($pid, $sid=null, $ssid=null)
    {
        return $this->router->generate("cms_" . $this->mode,
            array("pid" => $pid, "sid" => $sid, "ssid" => $ssid));
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
     * Fetch navigation sections from the database and return a heirarchical
     * array such tht it can be passed to a list style.
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
                $this->get_item_url($this->id_page, $id_item));
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
    private function fetch_section_hierarchy($id)
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
            if(!$this->has_access($this->mode, $id))
                continue;
            $url = ($item['action'] == null) ? "" : $this->get_item_url($id);
            array_push($res, $this->add_item($id, $item['keyword'], array(),
                $url));
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
            $children = $this->fetch_section_hierarchy($id);
            $id_root = $this->id_root_section;
            $id_child = $id;
            if($this->page_info['id_navigation_section'] == null)
            {
                $id_root = $id;
                $id_child = null;
            }
            $res[] = $this->add_item($id,
                $item['name'], $children,
                $this->get_item_url($this->id_page, $id_root, $id_child));
        }
        return $res;
    }

    /* Public Methods *********************************************************/

    /**
     * Check page access rights of the the current user given an ACL mode.
     *
     * @param string $mode
     *  A valid ACL mode.
     * @param int $pid
     *  The page id to check for.
     * @retval bool
     *  True if access is granted, false otherwise.
     */
    public function has_access($mode, $pid)
    {
        $acl_function = "has_access_". $mode;
        if(is_callable(array($this->acl, $acl_function)))
            return $this->acl->$acl_function($_SESSION['id_user'], $pid);
        return false;
    }

    public function get_field_info($name)
    {
        $sql = "SELECT f.id AS id, t.name AS type FROM fieldType AS t
            LEFT JOIN fields AS f on t.id = f.id_type
            WHERE f.name = :name";
        $info = $this->db->query_db_first($sql, array(":name" => $name));
        return $info;
    }

    /**
     * Get the current page acl mode.
     *
     * @retval string
     *  The current page acl mode.
     */
    public function get_mode()
    {
        return $this->mode;
    }

    /**
     * Return the page info array.
     *
     * @retval array
     *  The page info array with key => value pairs.
     */
    public function get_page_info()
    {
        return $this->page_info;
    }

    /**
     * Gets the active page fields from the database.
     *
     * @retval array
     *  An array of field arrays where each field has the following keys:
     *   'name':    the name of the field.
     *   'content': the content of the field.
     */
    public function get_page_fields()
    {
        return $this->db->fetch_page_fields_by_id($this->id_page);
    }

    /**
     * Gets the active section fields from the database.
     *
     * @retval array
     *  An array of field arrays where each field has the following keys:
     *   'name':    the name of the field.
     *   'content': the content of the field.
     */
    public function get_section_field_languages($id_section, $id_field)
    {
        $sql = "SELECT l.locale AS locale, l.id, sft.content
            FROM languages AS l
            LEFT JOIN sections_fields_translation AS sft
            ON l.id = sft.id_languages AND sft.id_sections = :sid
            AND sft.id_fields = :fid
            WHERE l.locale <> 'all' ";

        return $this->db->query_db($sql,
            array(":sid" => $id_section, ":fid" => $id_field));
    }

    public function get_section_independent_fields($id_section)
    {
        $sql = "SELECT CAST(f.id AS UNSIGNED) AS id, f.name, sft.content,
            ft.name AS type, CAST(sft.id_languages AS UNSIGNED) AS id_language
            FROM sections_fields_translation AS sft
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            LEFT JOIN languages AS l ON l.id = sft.id_languages
            LEFT JOIN fieldType AS ft ON ft.id = f.id_type
            WHERE sft.id_sections = :id AND l.locale = 'all'";
        return $this->db->query_db($sql,
            array(":id" => $this->get_active_section_id()));
    }

    public function get_section_language_fields($id_section)
    {
        $sql = "SELECT DISTINCT id_fields AS id, f.name, ft.name AS type
            FROM sections_fields_translation AS sft
            LEFT JOIN languages AS l ON id_languages = l.id
            LEFT JOIN fields AS f ON f.id = sft.id_fields
            LEFT JOIN fieldType AS ft ON ft.id = f.id_type
            WHERE id_sections = :id AND l.locale <> 'all'";
        return $this->db->query_db($sql, array(":id" => $id_section));
    }

    public function get_section_fields()
    {
        $id_section = $this->get_active_section_id();
        $fields = $this->get_section_independent_fields($id_section);
        $ids = $this->get_section_language_fields($id_section);
        foreach($ids as $id)
        {
            $contents = $this->get_section_field_languages($id_section, $id['id']);
            foreach($contents as $content)
                $fields[] = array(
                    "id" => intval($id['id']),
                    "id_language" => intval($content['id']),
                    "name" => $id['name'],
                    "locale" => $content['locale'],
                    "type" => $id['type'],
                    "content" => $content['content']
                );
        }
        return $fields;
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
        if($this->is_navigation_item())
        {
            $sql = "SELECT name FROM sections WHERE id = :id";
            $section = $this->db->query_db_first($sql,
                array(":id" => $this->id_root_section));
            $pages[] = $this->add_item($this->id_root_section, $section['name'],
                $this->fetch_section_hierarchy($this->id_root_section),
                $this->get_item_url($this->id_page, $this->id_root_section,
                    $this->id_root_section));
        }
        return $pages;
    }

    /**
     * Fetch navigation sections from the database and return a heirarchical
     * array such that it can be passed to a list style. If the current page
     * is NOT a navigation page, return an empty array.
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
        return $this->id_page;
    }

    /**
     * Gets the currently active root section id.
     *
     * @retval int
     *  The currently active root section id or null if there is no root
     *  section.
     */
    public function get_active_root_section_id()
    {
        if($this->page_info['id_navigation_section'] != null)
            return $this->id_root_section;
        return null;
    }

    /**
     * Gets the currently active section id.
     *
     * @retval int
     *  The currently active section id.
     */
    public function get_active_section_id()
    {
        if($this->id_section != null)
            return $this->id_section;
        else if($this->id_root_section != null)
            return $this->id_root_section;
        else
            return null;
    }

    /**
     * Checks whether the current page is a main page of a navigation set.
     *
     * @retval bool
     *  True if the current page is the main page of a navigation set, false
     *  otherwise.
     */
    public function is_navigation_main()
    {
        return ($this->page_info['id_navigation_section'] != null
            && $this->id_root_section == null);
    }

    /**
     * Checks whether the current page is an item of a navigation page.
     *
     * @retval bool
     *  True if the current page is an item of a navigation page, false
     *  otherwise.
     */
    public function is_navigation_item()
    {
        return ($this->page_info['id_navigation_section'] != null
            && $this->id_root_section != null);
    }

    public function update_db($id, $id_language, $fields)
    {
        $this->db->update_by_ids("sections_fields_translation", $fields,
            array(
                "id_sections" => $this->get_active_section_id(),
                "id_fields" => $id,
                "id_languages" => $id_language
            )
        );
    }
}
?>
