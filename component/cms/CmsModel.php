<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the cms component such
 * that the data can easily be displayed in the view of the component.
 */
class CmsModel extends BaseModel
{
    /* Private Properties *****************************************************/

    /**
     * All sections the user has select access to. The access rights of sections
     * are inherited from pages. This means that all sections associated to a
     * page are accessible if the page is accessible.
     */
    private $all_accessible_sections;

    /**
     * The current page id (the first url param).
     */
    private $id_page;

    /**
     * The current section id or root section id (the second url param).
     */
    private $id_root_section;

    /**
     * The current section id (the third url param).
     */
    private $id_section;

    /**
     * The current acl mode, i.e. 'select', 'insert', 'update', 'delete'.
     */
    private $mode;

    private $navigation_hierarchy;

    private $page_hierarchy;

    /**
     * The parameters of the curren page.
     */
    private $page_info;

    /**
     * A heirarchical array of all sections (static and navigation) associated
     * to the current page.
     */
    private $page_sections;

    /**
     * A hierarchical array of all sections associated to the current
     * navigation section.
     */
    private $page_sections_nav;

    /**
     * A hierarchical array of all secions associated to the current page
     * except those sections that are children of navigation a section.
     */
    private $page_sections_static;

    /**
     * Indicates the relation to the databse table to be updated.
     */
    private $relation;

    private $section_path;

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
    public function __construct($services, $params, $mode)
    {
        parent::__construct($services);
        $this->mode = $mode;
        $this->id_page = $params["pid"];
        $this->id_root_section = $params["sid"];
        $this->id_section = $params["ssid"];
        $this->relation = $params["type"];
        $this->id_delete = $params["did"];

        $this->page_info = $this->db->fetch_page_info_by_id($this->id_page);
        $this->section_path = array();
        $this->page_hierarchy = array();
        $this->navigation_hierarchy = array();
        $this->all_accessible_sections = array();
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
    private function add_list_item($id, $title, $children, $url)
    {
        return array(
            "id" => $id,
            "title" => $title,
            "children" => $children,
            "url" => $url
        );
    }

    /**
     * Add a navigation section to the section path.
     *
     * @param int $id
     *  The id of the section to be added.
     * @param string $name
     *  The name of the section to be added.
     */
    private function add_nav_to_section_path($id, $name)
    {
        $this->section_path[] = array(
            "url" => $this->get_link_url("cms_" . $this->mode, array(
                "pid" => $this->id_page,
                "sid" => $id
            )),
            "name" => $name
        );
    }

    /**
     * Add a page to the section path.
     *
     * @param int $id
     *  The id of the page to be added.
     * @param string $name
     *  The name of the page to be added.
     */
    private function add_page_to_section_path($id, $name)
    {
        $this->section_path[] = array(
            "url" => $this->get_link_url("cms_" . $this->mode, array(
                "pid" => $id
            )),
            "name" => $name
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
                $this->add_list_item($id, $item['keyword'], array(),
                    $this->get_item_url($id)));
        }
        return $root_items;
    }

    /**
     * Add a new list item.
     *
     * @param int $id
     *  The id of the field.
     * @param int $id_language
     *  The id of the language of the field.
     * @param string $name
     *  The name of the field.
     * @param string $locale
     *  The locale string of the language.
     * @param string type
     *  The type of the field.
     * @param string relation
     *  A string indication to what the field content relates. By this string
     *  the differnet db access actions are decided.
     * @param mixed $content
     *  The content of the field.
     */
    private function add_property_item($id, $id_language, $name, $locale, $type,
        $relation, $content)
    {
        return array(
            "id" => $id,
            "id_language" => $id_language,
            "name" => $name,
            "locale" => $locale,
            "type" => $type,
            "relation" => $relation,
            "content" => $content
        );
    }

    /**
     * Add a section to the section path.
     *
     * @param int $id
     *  The id of the section to be added.
     * @param string $name
     *  The name of the section to be added.
     */
    private function add_section_to_section_path($id, $name)
    {
        $this->section_path[] = array(
            "url" => $this->get_link_url("cms_" . $this->mode, array(
                "pid" => $this->id_page,
                "sid" => $this->id_root_section,
                "ssid" => $id
            )),
            "name" => $name
        );
    }

    /**
     * Fetch navigation sections from the database and return a heirarchical
     * array such that it can be passed to a list style. If the current page
     * is NOT a navigation page, return an empty array.
     *
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style (see CmsModel::add_list_item).
     */
    private function fetch_navigation_hierarchy()
    {
        if($this->is_navigation())
            return $this->fetch_navigation_items(
                $this->page_info['id_navigation_section']);
        return array();
    }

    /**
     * Fetch all sections associated to a page from the database and return a
     * heirarchical array such that it can be passed to a list style.
     *
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style.
     */
    private function fetch_page_sections()
    {
        $page_sections = $this->db->fetch_page_sections_by_id($this->id_page);
        return $this->prepare_section_list($page_sections);
    }

    /**
     * Fetch navigation sections from the database and return a heirarchical
     * array such tht it can be passed to a list style. This is a recursive
     * method.
     *
     * @param int $id
     *  The id of the parent section.
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style.
     */
    private function fetch_navigation_items($id, $recursion=true)
    {
        $res = array();
        $items_db = $this->db->fetch_nav_children($id);
        foreach($items_db as $item_db)
        {
            $id_item = intval($item_db['id']);
            if($recursion)
                $children = $this->fetch_navigation_items($id_item);
            else
                $children = array();
            $res[] = $this->add_list_item($id_item, $item_db['name'], $children,
                $this->get_item_url($this->id_page, $id_item));
        }
        return $res;
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
     * Fetch page data from the database and return a heirarchical array such
     * that it can be passed to a list style.
     *
     * @retval array
     *  A prepared hierarchical array of page items such that it can be passed
     *  to a list style (see CmsModel::add_list_item).
     */
    private function fetch_page_hierarchy()
    {
        $pages_db = $this->db->fetch_accessible_pages();
        $pages = $this->prepare_page_list_root($pages_db);
        return $this->add_page_list_children($pages, $pages_db);
    }

    /**
     * Fetch all sections associated to a navigation section from the database
     * and return a heirarchical array such that it can be passed to a list
     * style.
     *
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style.
     */
    private function fetch_page_sections_nav()
    {
        $pages = array();
        if($this->is_navigation_item())
        {
            $sql = "SELECT s.name FROM sections_navigation AS sn
                LEFT JOIN sections AS s ON sn.child = s.id
                WHERE sn.child = :id";
            $section = $this->db->query_db_first($sql,
                array(":id" => $this->id_root_section));
            if($section)
                $pages[] = $this->add_list_item($this->id_root_section,
                    $section['name'],
                    $this->fetch_section_hierarchy($this->id_root_section),
                    $this->get_item_url($this->id_page, $this->id_root_section,
                        $this->id_root_section));
        }
        return $pages;
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
     * Fetch the children of a section from the database and return a
     * heirarchical array such that it can be passed to a list style.
     *
     * @param int $id
     *  the id of the section to fetch the children
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style.
     */
    private function fetch_section_hierarchy($id, $recursion=true)
    {
        $db_sections = $this->db->fetch_section_children($id);
        return $this->prepare_section_list($db_sections, $recursion);
    }

    /**
     * Fetch all fields that are associated to the style of the specified
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
            ORDER BY ft.position, f.display, f.name";
        return $this->db->query_db($sql, array(":id" => $id));
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
        if($sid == $ssid) $ssid = null;
        return $this->router->generate("cms_" . $this->mode,
            array("pid" => $pid, "sid" => $sid, "ssid" => $ssid));
    }

    /**
     * Get the current last position of a list of children.
     *
     * @param array $sections
     *  The list of sections where the last position computed.
     * @retval int
     *  The last position number from the database or 0 if the list is empty.
     */
    private function get_last_position($sections)
    {
        $count = count($sections);
        if($count > 0)
            return intval($sections[$count-1]['position']) + 10;
        else
            return 0;
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
            array_push($res, $this->add_list_item($id, $item['keyword'], array(),
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
    private function prepare_section_list($items, $recursion = true)
    {
        $res = array();
        foreach($items as $item)
        {
            $id = intval($item['id']);
            if($recursion)
                $children = $this->fetch_section_hierarchy($id);
            else
                $children = array();
            $id_root = $this->id_root_section;
            $id_child = $id;
            if($this->page_info['id_navigation_section'] == null)
            {
                $id_root = $id;
                $id_child = null;
            }
            $res[] = $this->add_list_item($id,
                $item['name'], $children,
                $this->get_item_url($this->id_page, $id_root, $id_child));
        }
        return $res;
    }

    /**
     * Fetch all sections where the user has select access and add them to the
     * corresponding private property. The access rights of sections are
     * inherited from the pages.
     */
    private function set_all_accessible_sections()
    {
        $sql = "SELECT s.id, s.name, s.id_styles FROM sections AS s
            LEFT JOIN pages_sections AS ps ON ps.id_sections = s.id
            LEFT JOIN sections_navigation AS psn ON psn.child = s.id
            LEFT JOIN acl ON ps.id_pages = acl.id_pages OR psn.id_pages = acl.id_pages
            WHERE acl.acl_select = 1 AND id_users = :uid";
        $root_sections = $this->db->query_db($sql,
            array(":uid" => $_SESSION['id_user']));
        foreach($root_sections as $section)
        {
            $id = intval($section['id']);
            $this->all_accessible_sections[$id] = $this->add_list_item(
                array($id, intval($section['id_styles'])), $section['name'],
                array(), "");
            $this->set_section_children($id);
        }
        $name = array();
        foreach($this->all_accessible_sections as $key => $row)
            $name[$key] = $row['title'];
        array_multisort($name, SORT_ASC, $this->all_accessible_sections);
    }

    /**
     * Fetch all children of accessible sections and add them to the
     * corresponding private property.
     *
     * @param int $id
     *  The id of the section to fetch the children from.
     */
    private function set_section_children($id)
    {
        $children = $this->db->fetch_section_children($id);
        foreach($children as $child)
        {
            $id_child = intval($child['id']);
            $this->all_accessible_sections[$id_child] = $this->add_list_item(
                array($id_child, intval($child['id_styles'])),
                $child['name'], array(), "");
            $this->set_section_children($id_child);
        }
    }

    /**
     * Recursive function to add the current page and all parent pages to the
     * section path.
     *
     * @param array $pages
     *  A hierarchical array of pages.
     */
    private function set_section_path_pages($pages)
    {
        foreach($pages as $page)
        {
            if($page["id"] == $this->id_page
                || $this->set_section_path_pages($page['children']))
            {
                $this->add_page_to_section_path($page["id"], $page["title"]);
                return true;
            }
        }
        return false;
    }

    /**
     * Recursive function to add the current and all parent navigation sections
     * to the section path.
     *
     * @param array $sections
     *  A hierarchical array of sections.
     */
    private function set_section_path_nav($sections)
    {
        foreach($sections as $section)
        {
            if($section["id"] == $this->id_root_section
                || $this->set_section_path_nav($section['children']))
            {
                $this->add_nav_to_section_path($section["id"], $section["title"]);
                return true;
            }
        }
        return false;
    }

    /**
     * Recursive function to add the current and all parent sections to the
     * section path.
     *
     * @param array $sections
     *  A hierarchical array of sections.
     */
    private function set_section_path_sections($sections)
    {
        foreach($sections as $section)
        {
            if($section["id"] == $this->id_section
                || $this->set_section_path_sections($section['children']))
            {
                $this->add_section_to_section_path($section["id"], $section["title"]);
                return true;
            }
        }
        return false;
    }

    /**
     * Update the navigation order of a navigation section or page.
     *
     * @param int $id
     *  The parent section id (in case of a navigation page this is the field
     *  id_navigation_section).
     * @param array $order
     *  An indexed list of values where the index is the current position and
     *  the value the new poition number.
     * @retval bool
     *  True if the update operation is successful, false otherwise.
     */
    private function update_nav_order_db($id, $order)
    {
        if($order == "") return null;
        $orders = explode(',', $order);
        $children = $this->db->fetch_nav_children($id);
        $res = true;
        foreach($children as $index => $child)
        {
            $res &= $this->db->update_by_ids("sections_navigation",
                array("position" => $orders[$index]),
                array("parent" => $id, "child" => $child['id'])
            );
        }
        return $res;
    }

    /**
     * Update the order of the children of a page.
     *
     * @param int $id
     *  The id of the parent page.
     * @param array $order
     *  An indexed list of values where the index is the current position and
     *  the value the new poition number.
     * @retval bool
     *  True if the update operation is successful, false otherwise.
     */
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

    /**
     * Update a page field of the current page.
     *
     * @param int $id
     *  The id of the field to update.
     * @param int $id_language
     *  The id of the language of the field.
     * @param string $content
     *  The new content.
     * @retval bool
     *  True if the update operation is successful, false otherwise.
     */
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

    /**
     * Update the order of the children of a section.
     *
     * @param int $id
     *  The id of the parent section.
     * @param array $order
     *  An indexed list of values where the index is the current position and
     *  the value the new poition number.
     * @retval bool
     *  True if the update operation is successful, false otherwise.
     */
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

    /**
     * Update a field of the current section.
     *
     * @param int $id
     *  The id of the field to update.
     * @param int $id_language
     *  The id of the language of the field.
     * @param string $content
     *  The new content.
     * @retval bool
     *  True if the update operation is successful, false otherwise.
     */
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

    /* Public Methods *********************************************************/

    /**
     * Returns an array of all section that are accessible by the current user.
     *
     * @retval array
     *  a list of key => value pairs where the key is the id of a section and
     *  the value a list item (see CmsModel::add_list_item).
     */
    public function get_accessible_sections()
    {
        return $this->all_accessible_sections;
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
     * Prepare and return an  array with the current url parameters.
     *
     * @retval array
     *  An array with the keys:
     *   'pid':     The current page id.
     *   'sid':     The current root session id or the curren session id.
     *   'ssid':    The current session id.
     */
    public function get_current_url_params()
    {
        return array(
            "pid" => $this->id_page,
            "sid" => $this->id_root_section,
            "ssid" => $this->id_section
        );
    }

    /**
     * Get the id of the section to be removed.
     *
     * @retval string
     *  The id of section to be removed.
     */
    public function get_delete_id()
    {
        return $this->id_delete;
    }

    /**
     * Fetch information about a style field from the database.
     *
     * @param string $name
     *  The name of the field.
     * @retval array
     *  The db reuslt array with the following keys:
     *   'id':      The id of the field.
     *   'type':    The type of the field.
     */
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
        return $this->navigation_hierarchy;
    }

    /**
     * Get a hierarchical array of page items.
     *
     * @retval array
     *  A prepared hierarchical array of page items such that it can be passed
     *  to a list style (see CmsModel::add_list_item).
     */
    public function get_pages()
    {
        return $this->page_hierarchy;
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
     * Get an array of page properties. Page properties include the page title
     * and the list of associated sections and navigation sections. If multiple
     * languages are available, the title property is replicated for each
     * language.
     *
     * @retval array
     *  An array of fields where each field is defined in see
     *  CmsModel::add_property_item
     */
    public function get_page_properties()
    {
        $fields = array();
        $page_title = $this->fetch_page_field_languages($this->id_page, 2);
        foreach($page_title as $content)
        {
            $fields[] = $this->add_property_item(
                2, // label
                intval($content['id']),
                "title",
                $content['locale'],
                "text",
                "page_field",
                $content['content']
            );
        }
        $fields[] = $this->add_property_item(
            null,
            1, // all languages
            "sections",
            "",
            "style-list",
            "page_children",
            $this->page_sections_static
        );
        if($this->is_navigation())
            $fields[] = $this->add_property_item(
                null,
                1, // all languages
                "navigation",
                "",
                "style-list",
                "page_nav",
                $this->fetch_navigation_items(
                    $this->page_info['id_navigation_section'], false)
            );
        return $fields;
    }

    /**
     * Fetch all sections associated to a page from the database and return a
     * heirarchical array such that it can be passed to a list style.
     *
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style (see CmsModel::add_list_item()).
     */
    public function get_page_sections()
    {
        return $this->page_sections;
    }

    /**
     * Return the db relation.
     *
     * @retval string
     *  The db relation string.
     */
    public function get_relation()
    {
        return $this->relation;
    }

    /**
     * Return the section info array.
     *
     * @retval array
     *  The db result array.
     */
    public function get_section_info($section_id = null)
    {
        if($section_id == null) $section_id = $this->get_active_section_id();
        if($section_id == null) return null;
        return $this->db->fetch_section_info_by_id($section_id);
    }

    /**
     * Get an array of section fields, defined by the style associated to a
     * section. If multiple languages are available, each field is replicated
     * for each language except the 'all' language.
     *
     * @retval array
     *  An array of fields where each field is defined in see
     *  CmsModel::add_property_item
     */
    public function get_section_properties()
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
                $relation = "section_children";
                $contents = array(array(
                    "id" => $id,
                    "locale" => "",
                    "content" => $this->fetch_section_hierarchy($id_section, false),
                ));
            }
            else
                $contents = $this->fetch_section_field_independent($id_section,
                    $id);
            foreach($contents as $content)
            {
                $res[] = $this->add_property_item(
                    $id,
                    intval($content['id']),
                    $field['name'],
                    $content['locale'],
                    $field['type'],
                    $relation,
                    $content['content']
                );
            }
        }
        if($this->is_navigation_root_item())
            $res[] = $this->add_property_item(
                null,
                1, // all languages
                "navigation",
                "",
                "style-list",
                "section_nav",
                $this->fetch_navigation_items($id_section, false)
            );
        return $res;
    }

    /**
     * Fetch and return all styles from the database except navigation styles.
     *
     * @retval array
     *  The resulting db array.
     */
    public function get_style_list()
    {
        $sql = "SELECT id, name FROM styles WHERE id_type <> 3 ORDER BY name";
        return $this->db->query_db($sql);
    }

    public function get_section_path()
    {
        return $this->section_path;
    }

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

    /**
     * Insert a new section into the database.
     *
     * @param string $name
     *  The name of the new section.
     * @param int $id_style
     *  The id of the style associated to the new section.
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     * @retval int
     *  The id of the newly created section or false on failure.
     */
    public function insert_new_section($name, $id_style, $relation)
    {
        $res = true;
        if(!$this->acl->has_access_update($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        $new_id = $this->db->insert("sections", array(
            "name" => $name,
            "id_styles" => $id_style
        ));
        if(!$new_id) return false;
        $res &= $this->insert_section_link($new_id, $relation);
        if($res) return $new_id;
        else return false;
    }

    /**
     * Associate a section to another section, a page, or a navigation list.
     *
     * @param int $id
     *  The id of the section to create the link to.
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     * @retval bool
     *  True if the insert operation is successful, false otherwise.
     */
    public function insert_section_link($id, $relation)
    {
        if(!$this->acl->has_access_update($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        if($relation == "page_children")
        {
            $sections = $this->db->fetch_page_sections_by_id($this->id_page);
            return $this->db->insert("pages_sections", array(
                "id_pages" => $this->get_active_page_id(),
                "id_sections" => $id,
                "position" => $this->get_last_position($sections)
            ));
        }
        else if($relation == "section_children")
        {
            $sections = $this->db->fetch_section_children(
                $this->get_active_section_id());
            return $this->db->insert("sections_hierarchy", array(
                "parent" => $this->get_active_section_id(),
                "child" => $id,
                "position" => $this->get_last_position($sections)
            ));
        }
        else if($relation == "page_nav" || $relation == "section_nav")
        {
            if($relation == "page_nav")
                $id_parent = $this->page_info["id_navigation_section"];
            else if($relation == "section_nav")
                $id_parent = $this->get_active_section_id();
            $sections = $this->db->fetch_nav_children($id_parent);
            return $this->db->insert("sections_navigation", array(
                "parent" => $id_parent,
                "child" => $id,
                "id_pages" => $this->id_page,
                "position" => $this->get_last_position($sections)
            ));
        }
    }

    /**
     * Checks whether the current page is a navigation page.
     *
     * @retval bool
     *  True if the current page is a navigation page, false otherwise.
     */
    public function is_navigation()
    {
        return ($this->page_info['id_navigation_section'] != null);
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
    public function is_navigation_root_item()
    {
        return ($this->page_info['id_navigation_section'] != null
            && $this->id_root_section != null
            && $this->id_section == null);
    }

    /**
     * Remove a section link.
     *
     * @param int $id_section
     *  The id of the section the link is pointing to.
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     * @retval bool
     *  True if the delete operation is successful, false otherwise.
     */
    public function remove_section_association($id_section, $relation)
    {
        if(!$this->acl->has_access_delete($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        if($relation == "page_children")
            return $this->db->remove_by_ids("pages_sections", array(
                "id_pages" => $this->id_page,
                "id_sections" => $id_section
            ));
        else if($relation == "section_children")
            return $this->db->remove_by_ids("sections_hierarchy", array(
                "parent" => $this->get_active_section_id(),
                "child" => $id_section
            ));
        else if($relation == "page_nav" || $relation == "section_nav")
        {
            if($relation == "page_nav")
                $id_parent = $this->page_info["id_navigation_section"];
            else if($relation == "section_nav")
                $id_parent = $this->get_active_section_id();
            return $this->db->remove_by_ids("sections_navigation", array(
                "parent" => $id_parent,
                "child" => $id_section
            ));
        }
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
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     */
    public function update_db($id, $id_language, $content, $relation)
    {
        if(!$this->acl->has_access_update($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        if($relation == "page_field")
            return $this->update_page_fields_db($id, $id_language, $content);
        else if($relation == "section_field")
            return $this->update_section_fields_db($id, $id_language, $content);
        else if($relation == "section_children")
            return $this->update_section_children_order_db(
                $this->get_active_section_id(), $content);
        else if($relation == "page_children")
            return $this->update_page_children_order_db(
                $this->id_page, $content);
        else if($relation == "page_nav")
            return $this->update_nav_order_db(
                $this->page_info['id_navigation_section'], $content);
        else if($relation == "section_nav")
            return $this->update_nav_order_db(
                $this->get_active_section_id(), $content);
    }

    /**
     * This function allows to update some model properties only when needed
     * for insert opertaions. This is useful because the model is the same for
     * all mode operations.
     */
    public function update_insert_properties()
    {
        $this->set_all_accessible_sections();
    }

    /**
     * This function allows to update some model properties only when needed
     * for select and update opertaions. This allows to update the properties
     * after the controller modified the content.
     */
    public function update_select_properties()
    {
        $this->page_hierarchy = $this->fetch_page_hierarchy();
        $this->navigation_hierarchy = $this->fetch_navigation_hierarchy();
        $this->page_sections_static = $this->fetch_page_sections();
        $this->page_sections_nav = $this->fetch_page_sections_nav();
        $this->page_sections = $this->page_sections_static
            + $this->page_sections_nav;

        // prepare section path array
        $this->set_section_path_sections($this->page_sections_nav);
        array_pop($this->section_path);
        $this->set_section_path_nav($this->navigation_hierarchy);
        $this->set_section_path_pages($this->page_hierarchy);
        $this->section_path = array_reverse($this->section_path);
        $this->section_path[count($this->section_path)-1]["url"] = null;
    }
}
?>
