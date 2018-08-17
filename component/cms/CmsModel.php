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
    private $page_sections;
    private $all_accessible_sections;

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
        $this->page_sections = $this->get_page_sections();
        $this->all_accessible_sections = array();
        $this->set_all_accessible_sections();
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
    private function fetch_navigation_items($id)
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
     * Fetch all translations of the content of a specific page field,
     * except the 'all' language.
     *
     * @param int $id_page
     *  The id of the page the field is part of.
     * @param int $id_field
     *  The id of the field from which the translations shall be fetched.
     * @retval array
     *  An array of database items.
     */
    private function fetch_page_field_languages($id_page, $id_field)
    {
        $sql = "SELECT l.locale AS locale, l.id, pft.content
            FROM languages AS l
            LEFT JOIN pages_fields_translation AS pft
            ON l.id = pft.id_languages AND pft.id_pages = :pid
            AND pft.id_fields = :fid
            WHERE l.locale <> 'all'";

        return $this->db->query_db($sql,
            array(":pid" => $id_page, ":fid" => $id_field));
    }

    /**
     * Fetch all translations of the content of a specific section field,
     * except the 'all' language.
     *
     * @param int $id_section
     *  The id of the section the field is part of.
     * @param int $id_field
     *  The id of the field from which the translations shall be fetched.
     * @retval array
     *  An array of database items.
     */
    private function fetch_section_field_languages($id_section, $id_field)
    {
        $sql = "SELECT l.locale AS locale, l.id, sft.content
            FROM languages AS l
            LEFT JOIN sections_fields_translation AS sft
            ON l.id = sft.id_languages AND sft.id_sections = :sid
            AND sft.id_fields = :fid
            WHERE l.locale <> 'all'";

        return $this->db->query_db($sql,
            array(":sid" => $id_section, ":fid" => $id_field));
    }

    /**
     * Fetch the 'all' language content of a specific section field.
     *
     * @param int $id_section
     *  The id of the section the field is part of.
     * @param int $id_field
     *  The id of the field from which the translations shall be fetched.
     * @retval array
     *  An array with one database item.
     */
    private function fetch_section_field_independent($id_section, $id_field)
    {
        $sql = "SELECT l.locale AS locale, l.id, sft.content
            FROM languages AS l
            LEFT JOIN sections_fields_translation AS sft
            ON l.id = sft.id_languages AND sft.id_sections = :sid
            AND sft.id_fields = :fid
            WHERE l.locale = 'all'";

        return $this->db->query_db($sql,
            array(":sid" => $id_section, ":fid" => $id_field));
    }

    /**
     * Fetsch all fields that are associated to the style of the specified
     * section.
     *
     * @param int $id_section
     *  The id of the section the field is part of.
     * @retval array
     *  An array of database items.
     */
    private function fetch_style_fields_by_section_id($id)
    {
        $sql = "SELECT f.id, f.display, f.name, ft.name AS type
            FROM sections AS s
            LEFT JOIN styles AS st ON st.id = s.id_styles
            LEFT JOIN styles_fields AS sf ON sf.id_styles = st.id
            LEFT JOIN fields AS f ON f.id = sf.id_fields
            LEFT JOIN fieldType AS ft ON ft.id = f.id_type
            WHERE s.id = :id
            ORDER BY ft.position";
        return $this->db->query_db($sql, array(":id" => $id));
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
     * Return the section info array.
     *
     * @retval array
     *  The db result array.
     */
    public function get_section_info()
    {
        $section_id = $this->get_active_section_id();
        if($section_id == null) return null;
        return $this->db->fetch_section_info_by_id($section_id);
    }


    /**
     * Get an array of section fields, defined by the style associated to a
     * section. If multiple languages are available, each field is replicated
     * for each language except the 'all' language.
     *
     * @retval array
     *  An array of fields where each field has the following keys:
     *   'id':          The id of the field.
     *   'id_language': The id of the language of the field.
     *   'name':        The name of the field.
     *   'locale':      The locale string of the language.
     *   'type':        The type of the field.
     *   'content';     The content of the field.
     */
    public function get_section_fields()
    {
        $id_section = $this->get_active_section_id();
        $res = array();
        $fields = $this->fetch_style_fields_by_section_id($id_section);
        foreach($fields as $field)
        {
            $relation = "section_field";
            $id = intval($field['id']);
            if($field['display'] == '1')
                $contents = $this->fetch_section_field_languages($id_section,
                    $id);
            else if($field['type'] == "style-list")
            {
                $relation = "section_children_order";
                $contents = array(array(
                    "id" => $id,
                    "locale" => "",
                    "content" => $this->fetch_section_hierarchy($id_section),
                ));
            }
            else
                $contents = $this->fetch_section_field_independent($id_section,
                    $id);
            foreach($contents as $content)
            {
                $res[] = array(
                    "id" => $id,
                    "id_language" => intval($content['id']),
                    "name" => $field['name'],
                    "locale" => $content['locale'],
                    "type" => $field['type'],
                    "relation" => $relation,
                    "content" => $content['content']
                );
            }
        }
        return $res;
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
     * Fetch all sections associated to a page from the database and return a
     * heirarchical array such that it can be passed to a list style.
     *
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style.
     */
    public function get_page_sections($id_page = null)
    {
        if($id_page == null) $id_page = $this->id_page;
        $page_sections = $this->db->fetch_page_sections_by_id($id_page);
        $pages = $this->prepare_section_list($page_sections);
        if($this->is_navigation_item())
        {
            $sql = "SELECT name FROM sections WHERE id = :id";
            $section = $this->db->query_db_first($sql,
                array(":id" => $this->id_root_section));
            $pages[] = $this->add_item($this->id_root_section, $section['name'],
                $this->fetch_section_hierarchy($this->id_root_section),
                $this->get_item_url($id_page, $this->id_root_section,
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

    public function get_current_url_params()
    {
        $sid = $this->get_active_root_section_id();
        $ssid = $this->get_active_section_id();
        if($sid == null)
        {
            $sid = $ssid;
            $ssid = null;
        }
        return array(
            "pid" => $this->get_active_page_id(),
            "sid" => $sid,
            "ssid" => $ssid
        );
    }

    public function get_page_properties()
    {
        $fields = array();
        $page_title = $this->fetch_page_field_languages($this->id_page, 2);
        foreach($page_title as $content)
        {
            $fields[] = array(
                "id" => 2, // label
                "id_language" => intval($content['id']),
                "name" => "title",
                "locale" => $content['locale'],
                "type" => "text",
                "relation" => "page_field",
                "content" => $content['content']
            );
        }
        $fields[] = array(
            "id" => 12, // children
            "id_language" => 1,
            "name" => "sections",
            "locale" => "",
            "type" => "style-list",
            "relation" => "page_children_order",
            "content" => $this->get_page_sections()
        );
        return $fields;
    }

    private function set_all_accessible_sections()
    {
        $sections = array();
        $sql = "SELECT s.id, s.name, s.id_styles FROM sections AS s
            LEFT JOIN pages_sections AS ps ON ps.id_sections = s.id
            LEFT JOIN pages_sections_navigation AS psn ON psn.id_sections = s.id
            LEFT JOIN acl ON ps.id_pages = acl.id_pages OR psn.id_pages = acl.id_pages
            WHERE acl.acl_select = 1 AND id_users = :uid";
        $root_sections = $this->db->query_db($sql,
            array(":uid" => $_SESSION['id_user']));
        foreach($root_sections as $section)
        {
            $id = intval($section['id']);
            $this->all_accessible_sections[$id] = $this->add_item(
                array($id, intval($section['id_styles'])), $section['name'],
                array(), "");
            $this->set_section_children($id);
        }
        $name = array();
        foreach($this->all_accessible_sections as $key => $row)
            $name[$key] = $row['title'];
        array_multisort($name, SORT_ASC, $this->all_accessible_sections);
    }
    private function set_section_children($id)
    {
        $children = $this->db->fetch_section_children($id);
        foreach($children as $child)
        {
            $id_child = intval($child['id']);
            $this->all_accessible_sections[$id_child] = $this->add_item(
                array($id_child, intval($child['id_styles'])),
                $child['name'], array(), "");
            $this->set_section_children($id_child);
        }
    }

    private function fetch_root_section($id_section)
    {
        $res = array();
        $sql = "SELECT parent AS id FROM sections_hierarchy AS sh
            WHERE sh.child = :id";
        $parents = $this->db->query_db($sql, array(":id" => $id_section));
        if(!$parents) return $id_section;
        foreach($parents as $parent)
            $this->fetch_root_section($parent['id']);
    }

    public function get_accessible_sections()
    {
        return $this->all_accessible_sections;
    }

    public function get_style_list()
    {
        $sql = "SELECT id, name FROM styles ORDER BY name";
        return $this->db->query_db($sql);
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

    public function is_section_in_page($id_page, $id_section)
    {
        return $this->is_section_in_page_iteration(
            $this->get_page_sections($id_page),
            $id_section
        );
    }

    public function is_section_in_page_iteration($sections, $id_section)
    {
        foreach($sections as $section)
        {
            if($this->is_section_in_page_iteration($section['children'],
                    $id_section))
                return true;
            if($section['id'] == $id_section) return true;
        }
        return false;
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

    private function update_page_fields_db($id, $id_language, $content)
    {
        $update = array(
            "content" => $content
        );
        $insert = array(
            "content" => $content,
            "id_fields" => $id,
            "id_languages" => $id_language,
            "id_pages" => $this->id_page
        );
        return $this->db->insert("pages_fields_translation", $insert, $update);
    }

    private function update_section_fields_db($id, $id_language, $content)
    {
        $update = array(
            "content" => $content
        );
        $insert = array(
            "content" => $content,
            "id_fields" => $id,
            "id_languages" => $id_language,
            "id_sections" => $this->get_active_section_id()
        );
        return $this->db->insert("sections_fields_translation", $insert, $update);
    }

    private function update_section_children_order_db($id, $order)
    {
        if($order == "") return null;
        $orders = explode(',', $order);
        $children = $this->db->fetch_section_children($id);
        $res = true;
        foreach($children as $index => $child)
        {
            $res &= $this->db->update_by_ids("sections_hierarchy",
                array("position" => $orders[$index]),
                array("parent" => $id, "child" => $child['id'])
            );
        }
        return $res;
    }

    private function update_page_children_order_db($id, $order)
    {
        if($order == "") return null;
        $orders = explode(',', $order);
        $children = $this->db->fetch_page_sections_by_id($id);
        $res = true;
        foreach($children as $index => $child)
        {
            $res &= $this->db->update_by_ids("pages_sections",
                array("position" => $orders[$index]),
                array("id_pages" => $id, "id_sections" => $child['id'])
            );
        }
        return $res;
    }

    public function insert_section($name, $id_style)
    {
        $res = true;
        if(!$this->acl->has_access_update($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        $new_id = $this->db->insert("sections", array(
            "name" => $name,
            "id_styles" => $id_style
        ));
        if(!$new_id) return false;
        $res &= $this->insert_section_link($new_id);
        if($res) return $new_id;
        else return false;
    }

    public function insert_section_link($id)
    {
        if(!$this->acl->has_access_update($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        if($this->get_active_section_id() == null)
        {
            $sections = $this->db->fetch_page_sections_by_id($this->id_page);
            return $this->db->insert("pages_sections", array(
                "id_pages" => $this->get_active_page_id(),
                "id_sections" => $id,
                "position" => $this->get_last_position($sections)
            ));
        }
        else
        {
            $sections = $this->db->fetch_section_children(
                $this->get_active_section_id());
            return $this->db->insert("sections_hierarchy", array(
                "parent" => $this->get_active_section_id(),
                "child" => $id,
                "position" => $this->get_last_position($sections)
            ));
        }
    }

    private function get_last_position($sections)
    {
        $count = count($sections);
        if($count > 0)
            return intval($sections[$count-1]['position']) + 10;
        else
            return 0;
    }

    public function remove_section_association($id_section)
    {
        if(!$this->acl->has_access_insert($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        if($this->get_active_section_id() == null)
            return $this->db->remove_by_ids("pages_sections", array(
                "id_pages" => $this->id_page,
                "id_sections" => $id_section
            ));
        else
            return $this->db->remove_by_ids("sections_hierarchy", array(
                "parent" => $this->get_active_section_id(),
                "child" => $id_section
            ));
    }

    /**
     * Update the database, given the field data from one field.
     *
     * @param int $id
     *  The id of the field to update.
     * @param int $id_language
     *  The id of the language of the field to update.
     * @param string $content
     *  The content of the field to be updated.
     */
    public function update_db($id, $id_language, $content, $relation)
    {
        if(!$this->acl->has_access_update($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        if($relation == "page_field")
            return $this->update_page_fields_db($id, $id_language, $content);
        else if($relation == "section_field")
            return $this->update_section_fields_db($id, $id_language, $content);
        else if($relation == "section_children_order")
            return $this->update_section_children_order_db(
                $this->get_active_section_id(), $content);
        else if($relation == "page_children_order")
            return $this->update_page_children_order_db(
                $this->id_page, $content);
    }
}
?>
