<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     * All reference sections the user has select access to. The access rights of sections
     * are inherited from pages. This means that all sections associated to a
     * page are accessible if the page is accessible.
     */
    private $all_reference_sections;

    /**
     * All sections that are not assigned to any page.
     */
    private $all_unassigned_sections;

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

    /**
     * The current update mode, i.e. 'insert', 'update', 'delete'.
     */
    private $update_mode;

    /**
     * A hierarchical array storing the navigation section items.
     */
    private $navigation_hierarchy;

    /**
     * A hierarchical array storing the page items.
     */
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

    /**
     * An array of items that correspond to the section path, i.e. the current
     * active section and all its parents.
     */
    private $section_path;

    /**
     * The ID of the cms page
     */
    private $id_cms_page;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   - 'pid':     The id of the page that is currently edited.
     *   - 'sid':     The root id of a page or the section that is currently
     *                selected.
     *   - 'ssid':    The id of the section that is currently selected
     *                (only relevant for navigation pages).
     *   - 'did':     The id of a section to be deleted (only relevant in delete
     *                mode).
     *   - 'type':    This describes the database relation in order to know
     *                wheter to access pages, sections, navigations.
     * @param string $mode
     *  The mode of the page: 'select', 'update', 'insert', or 'delete'
     * @param number $id_cms_page
     *  The id of the current cms page being loaded
     */
    public function __construct($services, $params, $mode, $id_cms_page)
    {
        parent::__construct($services);
        $this->id_cms_page = $id_cms_page;
        $this->mode = $mode;
        $this->id_page = isset($params["pid"]) ? intval($params["pid"]) : null;
        $this->id_root_section = (isset($params["sid"]) && $params["sid"] != 0)
            ?  intval($params["sid"]) : null;
        $this->id_section = (isset($params["ssid"]) && $params["ssid"] != 0)
            ?  intval($params["ssid"]) : null;
        $this->relation = isset($params["type"]) ? $params["type"] : "prop";
        $this->update_mode = isset($params["mode"]) ? $params["mode"] : "update";
        $this->id_delete = (isset($params["did"]) && $params["did"] != 0)
            ?  intval($params["did"]) : null;

        $this->page_info = $this->db->fetch_page_info_by_id($this->id_page);
        $this->section_path = array();
        $this->page_hierarchy = array();
        $this->navigation_hierarchy = array();
        $this->all_accessible_sections = array();
        $this->all_reference_sections = array();
        $this->set_all_reference_sections();
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
     * @param int $parent_id
     * the parent id of the section
     * @param string $relation
     * the relation of the section. Is it attached to page or another section, etc
     */
    private function add_list_item($id, $title, $children, $url, $parent_id = null, $relation = null)
    {
        return array(
            "id" => $id,
            "title" => $title,
            "children" => $children,
            "url" => $url,
            "parent_id" => $parent_id,
            "relation" => $relation,
        );
    }

    /**
     * Add children to the root page list
     *
     * @param array $root_items
     *  An array of prepared (i.e. put into a form such that they can be passed
     *  to a list style) root page items.
     * @param array $items
     *  The page items as they are returned from the db query.
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
            $title = ($item['nav_position'] != '' ? '<i class="fas fa-bars"></i> ' : '') . $item['keyword']; // add menu icon for the pages that are in the menu
            array_push($root_items[$root_idx]['children'],
                $this->add_list_item($id, $title, array(),
                    $this->get_item_url($id)));
        }
        return $root_items;
    }

    /**
     * Add a new list item.
     * 
     * @param array $field
     * filed object with properties described under
     * @param int $id
     *  The id of the field.
     * @param int $id_language
     *  The id of the language of the field.
     * @param int $id_gender
     *  The id of the target gender of the field.
     * @param string $name
     *  The name of the field.
     * @param string $help
     *  A description of what the field is for.
     * @param string $locale
     *  The locale string of the language.
     * @param string $type
     *  The type of the field as defined in the db table fieldType.
     * @param string $relation
     *  A string indication to what the field content relates. By this string
     *  the different db access actions are decided.
     * @param mixed $content
     *  The content of the field.
     * @param string $gender
     *  The gender the content is associated with.
     * @param int $hidden
     *  1 the field is hidden 0 not
     * @param int $is_required
     *  1 the field needs a value, 2 it does not need a value
     * @param string $pattern
     * if there is some special pattern for the field
     * @param string $label
     * if the value is set it will be used as label
     */
    private function add_property_item($field)
    {
        return array(
            "id" => isset($field['id'])?$field['id']:null,
            "id_language" => isset($field['id_language'])?$field['id_language']:ALL_LANGUAGE_ID,
            "id_gender" => isset($field['id_gender'])?$field['id_gender']:MALE_GENDER_ID,
            "name" => $field['name'],
            "help" => isset($field['help']) ?  $this->parsedown->text($field['help']) : '',
            "locale" => isset($field['locale'])?$field['locale']:'',
            "type" => $field['type'],
            "relation" => $field['relation'],
            "content" => $field['content'],
            "gender" => isset($field['gender'])?$field['gender']:'',
            "hidden" => isset($field['hidden'])?$field['hidden']:0,
            "is_required" => isset($field['is_required'])?$field['is_required']:0,
            "format" => isset($field['format'])?$field['format']:'',
            "label" => isset($field['label'])?$field['label']:''
        );
    }

    /**
     * Creates a new navigation section that can then be linked to the new page.
     *
     * @param string $keyword
     *  The keyword of the page for which the navigation page is created.
     * @retval int
     *  The id of the newly crated navigation section.
     */
    private function create_new_navigation_section($keyword)
    {
        return $this->db->insert("sections", array(
            "name" => $keyword . "-navigation",
            "id_styles" => NAVIGATION_STYLE_ID,
        ));
    }

    /**
     * Fetch all section data from the database for sections that are not
     * assigned to any page and return a heirarchical array such that it can be
     * passed to a list style.
     *
     * @retval array
     *  A prepared hierarchical array of unassigned section items such that it
     *  can be passed to a list style.
     */
    public function fetch_unassigned_sections()
    {
        if($this->relation === RELATION_PAGE_NAV || $this->relation === RELATION_SECTION_NAV)
            $where = "AND st.id = :sid";
        else
            $where = "AND st.id != :sid";
        $sections = array();
        $sql = "SELECT s.id, s.name, s.id_styles FROM sections AS s
            LEFT JOIN styles AS st ON st.id = s.id_styles
            LEFT JOIN sections_hierarchy AS sh ON s.id = sh.child
            LEFT JOIN pages_sections AS ps ON s.id = ps.id_sections
            LEFT JOIN sections_navigation AS sn ON s.id = sn.child
            WHERE sh.child IS NULL AND ps.id_sections IS NULL
            AND sn.child IS NULL AND (s.owner IS NULL OR s.owner = :uid)
            AND st.id_type <> 3 $where
            ORDER BY s.name";
        $sections_db = $this->db->query_db($sql, array(
            ":uid" => $_SESSION["id_user"],
            ":sid" => NAVIGATION_CONTAINER_STYLE_ID,
        ));
        foreach($sections_db as $section)
        {
            $id = intval($section['id']);
            $sections[$id] = $this->add_list_item(
                array($id, intval($section['id_styles'])), $section['name'],
                array(), "");
        }
        return $sections;
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
     * array such taht it can be passed to a list style. This is a recursive
     * method.
     *
     * @param int $id
     *  The id of the parent section.
     * @param int $recursion
     *  When set to true the method is executed recursively and a heiarchical
     *  array is produced. When set to false the method is only executing once
     *  and, hence, returns a only items from one hierarchy level.
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
     * Fetch all fields that are associated to a page.
     *
     * @param int $id
     *  The id of the page.
     * @retval array
     *  An array of database items.
     */
    private function fetch_page_fields($id)
    {
        $lang = isset($_SESSION['cms_language']) ? $_SESSION['cms_language'] : 1;
        $sql = "SELECT 1*p.id AS id_pages, 1*f.id AS id, f.display, f.name, ft.name AS type,
        pf.default_value, pf.help, l.locale AS locale, 1*l.id AS id_language,
        'male' AS gender, ".MALE_GENDER_ID." AS id_gender,
        (SELECT content FROM pages_fields_translation AS pft WHERE pft.id_pages = p.id AND id_fields = f.id AND id_languages = l.id LIMIT 0,1) AS content,
        :relation AS relation, 0 as hidden
        FROM pages AS p
        LEFT JOIN pageType pt ON (pt.id = p.id_type)
        LEFT JOIN pageType_fields AS pf ON (pf.id_pageType = pt.id)
        LEFT JOIN fields AS f ON f.id = pf.id_fields
        LEFT JOIN fieldType AS ft ON ft.id = f.id_type        
        JOIN languages AS l ON ((l.id = 1 AND f.display = 0) OR (f.display = 1 AND l.id > 1))
        WHERE p.id = :id
        HAVING (id_language = 1 OR id_language IN (".$lang."))
        ORDER BY ft.position, f.display, f.name";
        return $this->db->query_db($sql, array(
            ":id" => $id, ":relation" => RELATION_PAGE_FIELD
        ));
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
            if ($section) {
                $pages[] = $this->add_list_item(
                    $this->id_root_section,
                    $section['name'],
                    $this->fetch_section_hierarchy($this->id_root_section),
                    $this->get_item_url($this->id_page, $this->id_root_section, $this->id_root_section)
                );
            }
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
        $sql = "SELECT l.locale AS locale, l.id AS id_language, sft.content,
            '' AS gender, '1' AS id_gender
            FROM languages AS l
            LEFT JOIN sections_fields_translation AS sft
            ON l.id = sft.id_languages AND sft.id_sections = :sid
            AND sft.id_fields = :fid
            WHERE l.locale = 'all'";

        return $this->db->query_db($sql,
            array(":sid" => $id_section, ":fid" => $id_field));
    }

    /**
     * Fetch the children of a section from the database and return a
     * heirarchical array such that it can be passed to a list style.
     *
     * @param int $id
     *  the id of the section to fetch the children
     * @param int $recursion
     *  When set to true the method is executed recursively and a heiarchical
     *  array is produced. When set to false the method is only executing once
     *  and, hence, returns a only items from one hierarchy level.
     * @retval array
     *  A prepared hierarchical array of section items such that it can
     *  be passed to a list style.
     */
    public function fetch_section_hierarchy($id, $recursion=true)
    {
        $db_sections = $this->db->fetch_section_children($id);
        return $this->prepare_section_list($db_sections, $recursion);
    }

    /**
     * Fetch all fields that are associated to the style of the specified
     * section.
     *
     * @param int $id
     *  The id of the section the field is part of.
     * @retval array
     *  An array of database items.
     */
    private function fetch_style_fields_by_section_id($id)
    {
        $lang = isset($_SESSION['cms_language']) ? $_SESSION['cms_language'] : 1;
        $gender = isset($_SESSION['cms_gender']) ? $_SESSION['cms_gender'] : 1;
        $sql = "SELECT 1*s.id AS id_sections, 1*f.id AS id, f.display, sf.hidden, f.name, ft.name AS type,
        sf.default_value, sf.help, l.locale AS locale, 1*l.id AS id_language, 
        IFNULL((SELECT content FROM sections_fields_translation AS sft WHERE sft.id_sections = s.id AND sft.id_fields = f.id AND sft.id_languages = l.id AND sft.id_genders = g.id LIMIT 0,1), sf.default_value) AS content,
        g.name AS gender, 1*g.id AS id_gender,
        CASE
            WHEN ft.name = 'style-list' THEN :RELATION_SECTION_CHILDREN
            ELSE :RELATION_SECTION_FIELD
        END AS relation, s.name AS section_name, st.name AS style_name
        FROM sections AS s
        LEFT JOIN styles AS st ON st.id = s.id_styles
        LEFT JOIN styles_fields AS sf ON sf.id_styles = st.id
        LEFT JOIN fields AS f ON f.id = sf.id_fields
        LEFT JOIN fieldType AS ft ON ft.id = f.id_type
        JOIN genders g on (g.id = 1 or (f.display = 1))
        JOIN languages AS l ON ((l.id = 1 AND f.display = 0) OR (f.display = 1 AND l.id > 1))
        WHERE s.id = :id AND sf.disabled = 0
        HAVING id_gender IN (" . $gender . ") AND (id_language = 1 OR id_language IN (" . $lang . "))
        ORDER BY ft.position, f.display, f.name";
        return $this->db->query_db($sql, array(
            ":id" => $id,
            ":RELATION_SECTION_CHILDREN" => RELATION_SECTION_CHILDREN,
            ":RELATION_SECTION_FIELD" => RELATION_SECTION_FIELD,
        ));
    }

    /**
     * Return the index of an item, given its id.
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
        if ($sid == $ssid) $ssid = null;
        if ($this->get_services()->get_user_input()->is_new_ui_enabled() && $this->is_link_active("cmsUpdate")) {
            return $this->router->generate("cmsUpdate", array("pid" => $pid, "sid" => $sid, "ssid" => $ssid, "mode" => UPDATE, "type" => "prop"));
        } else {
            return $this->router->generate(
                "cmsSelect",
                array("pid" => $pid, "sid" => $sid, "ssid" => $ssid)
            );
        }
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
            $url = $this->get_item_url($id);
            $title = ($item['nav_position'] != '' ? '<i class="fas fa-bars"></i> ' : '') . $item['keyword']; // add menu icon for the pages that are in the menu
            array_push($res, $this->add_list_item($id, $title, array(),$url));
        }
        return $res;
    }

    /**
     * Prepare an array with section items such that it can be passed to a list
     * style.
     *
     * @param array $items
     *  The section items as they are returned from the db query.
     * @param int $recursion
     *  When set to true the method is executed recursively and a heiarchical
     *  array is produced. When set to false the method is only executing once
     *  and, hence, returns a only items from one hierarchy level.
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
            $res[] = $this->add_list_item(
                $id,
                $item['name'],
                $children,
                $this->get_item_url($this->id_page, $id_root, $id_child),
                $item['parent_id'],
                $item['relation']
            );
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
        $sql = "SELECT s.id, s.name, s.id_styles,
            COALESCE(ps.id_pages, psn.id_pages) AS pid
            FROM sections AS s
            LEFT JOIN pages_sections AS ps ON ps.id_sections = s.id
            LEFT JOIN sections_navigation AS psn ON psn.child = s.id
            LEFT JOIN pages AS pp ON pp.id = ps.id_pages
            LEFT JOIN pages AS pn ON pn.id = psn.id_pages
            WHERE (pp.id_type = 3 OR pn.id_type = 3)
            AND (ps.id_pages IS NOT NULL OR psn.id_pages IS NOT NULL)";
        $root_sections = $this->db->query_db($sql);
        foreach($root_sections as $section)
        {
            if(!$this->acl->has_access_select($_SESSION['id_user'],
                    $section['pid']))
                continue;
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
     * Fetch all reference sections where the user has select access and add them to the
     * corresponding private property. The access rights of sections are
     * inherited from the pages.
     */
    private function set_all_reference_sections()
    {
        $sql = "SELECT s.id, s.name, s.id_styles,
        COALESCE(ps.id_pages, psn.id_pages) AS pid
        FROM sections AS s
        INNER JOIN styles st on(s.id_styles = st.id)
        LEFT JOIN pages_sections AS ps ON ps.id_sections = s.id
        LEFT JOIN sections_navigation AS psn ON psn.child = s.id
        LEFT JOIN pages AS pp ON pp.id = ps.id_pages
        LEFT JOIN pages AS pn ON pn.id = psn.id_pages
        WHERE st.name = 'refContainer' ";
        $ref_sections = $this->db->query_db($sql);
        $sections = array();
        foreach($ref_sections as $section)
        {
            $id = intval($section['id']);
            $sections[$id] = $this->add_list_item(
                array($id, intval($section['id_styles'])), $section['name'],
                array(), "");
        }
        $this->all_reference_sections = $sections;
    }

    /**
     * Sets the default acl rights for the newly created page.
     *
     * @param int $pid
     *  The id of the page.
     * @param bool $is_open
     *  If set select access to the guest user is granted.
     */
    private function set_new_page_acl($pid, $is_open = false)
    {
        $this->acl->grant_access_levels(ADMIN_GROUP_ID, $pid, 4, true);
        $this->acl->grant_access_levels(EXPERIMENTER_GROUP_ID, $pid, 1, true);
        $this->acl->grant_access_levels(SUBJECT_GROUP_ID, $pid, 1, true);
        $this->acl->grant_access_levels($_SESSION['id_user'], $pid, 4, false);
        //TO DO grant access to creator
        if($is_open)
            $this->acl->grant_access_levels(GUEST_USER_ID, $pid, 1, false);
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
     * Recursive function to add the current page or section item and all its
     * parents to the section path.
     *
     * @param array $items
     *  A hierarchical array of page or section items (see
     *  CmsModel:add_list_item).
     * @param int $id
     *  The id of the page or section to search for.
     * @retval bool
     *  True if the item was found in the list of children of a parent item,
     *  false otherwise.
     */
    private function set_section_path($items, $id)
    {
        foreach($items as $item)
        {
            if($item["id"] == $id
                || $this->set_section_path($item['children'], $id))
            {
                $this->section_path[] = $item;
                return true;
            }
        }
        return false;
    }

    /**
     * Recursive function to add the current page and all parent pages to the
     * section path.
     *
     * @param array $pages
     *  A hierarchical array of pages.
     * @retval bool
     *  True if the item was found in the list of children of a parent item,
     *  false otherwise.
     */
    private function set_section_path_pages($pages)
    {
        foreach($pages as $page)
        {
            if($page["id"] == $this->id_page
                || $this->set_section_path_pages($page['children']))
            {
                $this->section_path[] = $page;
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
     * @retval bool
     *  True if the item was found in the list of children of a parent item,
     *  false otherwise.
     */
    private function set_section_path_nav($sections)
    {
        foreach($sections as $section)
        {
            if($section["id"] == $this->id_root_section
                || $this->set_section_path_nav($section['children']))
            {
                $this->section_path[] = $section;
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
     * @retval bool
     *  True if the item was found in the list of children of a parent item,
     *  false otherwise.
     */
    private function set_section_path_sections($sections)
    {
        foreach($sections as $section)
        {
            print_r($section);
            if($section["id"] == $this->id_section
                || $this->set_section_path_sections($section['children']))
            {
                $this->section_path[] = $section;
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
     * @param string $order
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
     * @param string $order
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
     * @param string $order
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
     * Adjust the hierarchy after an element is removed
     *
     * @param int $id_section
     *  The id of the section the link is pointing to.
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     * 
     */
    public function adjust_hierarchy($id_section, $relation)
    {
        $sql = "SET @row_number = -1;  
                UPDATE sections_hierarchy
                SET position = (@row_number:=@row_number + 1) * 10
                WHERE parent = :id_section
                ORDER by position;";
        if ($relation == RELATION_PAGE_CHILDREN) {
            $sql = "SET @row_number = -1;  
                UPDATE pages_sections
                SET position = (@row_number:=@row_number + 1) * 10
                WHERE id_pages = :id_section
                ORDER by position;";
        }
        $this->db->execute_update_db($sql, array(":id_section" => $id_section));
    }

    /* Public Methods *********************************************************/

    /**
     * Checks whether the current user is allowed to delete pages.
     *
     * @retval bool
     *  True if the current user can delete pages, false otherwise.
     */
    public function can_delete_page()
    {
        return $this->acl->has_access_delete($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("cmsDelete"));
    }

    /**
     * Checks whether the current user is allowed to delete sections.
     *
     * @retval bool
     *  True if the current user can delete sections, false otherwise.
     */
    public function can_delete_section()
    {
        $style_group = $this->get_style_group($this->get_active_section_id());
        return $style_group['id'] != STYLE_GROUP_INTERN_ID;
    }

    /**
     * Checks whether the current user is allowed to create new child pages.
     *
     * @retval bool
     *  True if the current user can create new child pages, false otherwise.
     */
    public function can_create_new_child_page()
    {
        return ($this->can_create_new_page()
            && $this->get_active_section_id() == null
            && $this->page_info['parent'] == null
            && ($this->page_info['id_type'] == EXPERIMENT_PAGE_ID
                || $this->page_info['id_type'] == OPEN_PAGE_ID));
    }

    /**
     * Checks whether the current user is allowed to create new pages.
     *
     * @retval bool
     *  True if the current user can create new pages, false otherwise.
     */
    public function can_create_new_page()
    {
        return $this->acl->has_access_insert($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("cmsInsert"));
    }

    /**
     * Checks whether the current user is allowed to import sections.
     *
     * @retval bool
     *  True if the current user can import sections, false otherwise.
     */
    public function can_import_section()
    {
        return $this->acl->has_access_insert($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("cmsImport"));
    }

    /**
     * Checks whether the current user is allowed to export sections.
     *
     * @retval bool
     *  True if the current user can export sections, false otherwise.
     */
    public function can_export_section()
    {
        return $this->id_root_section > 0 && $this->acl->has_access_select($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("cmsExport"));
    }

    /**
     * Inserts a new page to the bd and sets the basic access rights.
     *
     * @param string $keyword
     *  The unique keyword of the page
     * @param string $url
     *  The url pattern of the page
     * @param string $protocol
     *  The pipe-delimited string of protocols
     * @param int $action
     *  Indigates the page action or whether the page is a navigation page.
     * @param string $position
     *  The position string if the new page should appear in the navbar, null
     *  otherwise.
     * @param bool $is_headless
     *  If set to true the page has no header or footer. If set to false the
     *  page is rendered with header and footer.
     * @param bool $is_open
     *  If set to true the page is accessible by anyone. If set to false a login
     *  is required.
     * @param int $parent
     *  The id of the parent page or null if a root page is created.
     * @param int $id_pageAccessTypes
     *  Page type access. It could be mobile, web or mobile_web
     * @retval int
     *  The id of the created page.
     */
    public function create_new_page($keyword, $url, $protocol, $action,
        $position, $is_headless, $is_open, $parent, $id_pageAccessTypes)
    {
        $nav_id = null;
        $page_type = $is_open ? OPEN_PAGE_ID : EXPERIMENT_PAGE_ID;
        if($action == 4)
        {
            $action = 3;
            $nav_id = $this->create_new_navigation_section($keyword);
        }
        $pid = $this->db->insert("pages", array(
            "keyword" => $keyword,
            "url" => $url,
            "protocol" => $protocol,
            "id_navigation_section" => $nav_id,
            "id_actions" => $action,
            "id_type" => $page_type,
            "nav_position" => $position ? 999 : null,
            "parent" => $parent,
            "is_headless" => $is_headless ? 1 : 0,
            "id_pageAccessTypes" => $id_pageAccessTypes
        ));
        $this->set_new_page_acl($pid, $is_open);
        if($position)
            $this->update_page_order($position, $parent);
        return $pid;
    }

    /**
     * Permanently delete a page from the db.
     *
     * @param int $pid
     *  The id of the page to delete.
     * @retval bool
     *  True if the process was successful, false otherwise.
     */
    public function delete_page($pid)
    {
        $res = true;
        $info = $this->get_page_info($pid);
        if($info['id_navigation_section'] != null)
            $res = $this->delete_section($info['id_navigation_section']);
        if($res)
            return $this->db->remove_by_fk("pages", "id", $pid);
        return false;
    }

    /**
     * Permanently delete a section from the db.
     *
     * @param int $sid
     *  The id of the section to delete.
     * @retval bool
     *  True if the process was successful, false otherwise.
     */
    public function delete_section($sid)
    {
        return $this->db->remove_by_fk("sections", "id", $sid);
    }

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
     * Returns an array of all reference section that are accessible by the current user.
     *
     * @retval array
     *  a list of key => value pairs where the key is the id of a section and
     *  the value a list item (see CmsModel::add_list_item).
     */
    public function get_reference_sections()
    {
        return $this->all_reference_sections;
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
     * Getter function for the cms page id.
     *
     * @retval number
     *  The ID of the cms page
     */
    public function get_cms_page_id()
    {
        return $this->id_cms_page;
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
            "ssid" => $this->id_section,
            "did" => $this->id_delete,
            "mode" => $this->update_mode,
            "type" => $this->relation,
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
     * Fetch language information from the database.
     *
     * @param int $id
     *  The id of the language to fetch.
     * @retval array
     *  The db array with the following keys:
     *   - 'id':          The id of the fetched language.
     *   - 'locale':      The language short notation.
     *   - 'language':    The language long notation.
     */
    public function get_language($id)
    {
        return $this->db->select_by_uid("languages", $id);
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
     * Fetch all non-internal root pages from the db which are shown in the
     * header.
     *
     * @param int $parent
     *  The id of the parent page or null to get root pages.
     * @return array
     *  An array of page items with the following keys:
     *   'id'       The id of the page.
     *   'title'    The keyword of the page.
     */
    public function get_pages_header($parent = null)
    {
        $parent_clause = $parent ? "AND parent = :pid" : "AND parent is NULL";
        $sql = "SELECT id, keyword AS title FROM pages
            WHERE nav_position IS NOT NULL AND id_type <> 1
            $parent_clause
            ORDER BY nav_position";
        return $this->db->query_db($sql, array(":pid" => $parent));
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
        $res = array();
        $res[] = $this->add_property_item(
            array(
                "name" => "keyword",
                "help" => "The page keyword must be unique, otherwise the page creation will fail. <b>Note that the page keyword can contain numbers, letters, - and _ characters</b>",
                "type" => "text",
                "relation" => RELATION_PAGE,
                "content" => $this->page_info['keyword'],
                "is_required" => 1,
                "format" => "[a-zA-Z0-9_-]+",
                "label" => "Keyword",
            )
        );
        $res[] = $this->add_property_item(
            array(
                "name" => "id_pageAccessTypes",
                "help" => "Select for what content the page will be loaded",
                "type" => "select-platform",
                "relation" => RELATION_PAGE,
                "content" => $this->page_info['id_pageAccessTypes'],
                "label" => "Page Access Type",
            )
        );
        $res[] = $this->add_property_item(
            array(
                "name" => "is_headless",
                "help" => "A headless page will <b>not</b> render any header or footer.",
                "type" => "checkbox",
                "relation" => RELATION_PAGE,
                "content" => $this->page_info['is_headless'],
                "label" => "Headless Page",
            )
        );
        $res = array_merge($res, $this->fetch_page_fields($this->id_page));
        if($this->is_navigation())
        {
            // add navigation section fields
            $section_fields = $this->get_section_properties(
                    $this->page_info['id_navigation_section']);
            foreach($section_fields as $section_field)
                $res[] = $section_field;
        }
        if ($this->page_info['action'] === "sections") {
            $res[] = $this->add_property_item(
                array(
                    "name" => "sections",
                    "label" => "Sections",
                    "type" => "style-list",
                    "relation" => RELATION_PAGE_CHILDREN,
                    "content" => $this->page_sections_static,
                )
            );
        }
        if ($this->is_navigation()) {
            $res[] = $this->add_property_item(
                array(
                    "name" => "navigation",
                    "label" => "Navigation",
                    "type" => "style-list",
                    "relation" => RELATION_PAGE_NAV,
                    "content" => $this->fetch_navigation_items($this->page_info['id_navigation_section'], false)
                )
            );
        }
        return $res;
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
     * @param int $section_id
     *  The id of a section. If no id is provided the current section id is
     *  used.
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
    public function get_section_properties($id_section=null)
    {
        if($id_section == null)
            $id_section = $this->get_active_section_id();
        $res = array();
        $fields = $this->fetch_style_fields_by_section_id($id_section);
        $res[] = $this->add_property_item(
            array(
                "name" => "section_name",
                "help" => "Section name. <b>Note that the section name can contain only numbers, letters, - and _ characters</b>",
                "type" => "text",
                "relation" => RELATION_SECTION,
                // "content" => str_replace('-'.$fields[0]['style_name'], '',$fields[0]['section_name'] ), // normalize the name - remove the style name affix
                "content" => $fields[0]['section_name'],
                "is_required" => 0,
                "format" => "[a-zA-Z0-9_-]+",
                "label" => "Section name",
            )
        );        
        $res = array_merge($res, $fields);
        if ($this->is_navigation_root_item()) {
            $res[] = $this->add_property_item(
                array(
                    "name" => "navigation",
                    "label" => "Navigation",
                    "type" => "style-list",
                    "relation" => RELATION_SECTION_NAV,
                    "content" => $this->fetch_navigation_items($id_section, false)
                )
            );
        }
        return $res;
    }

    /**
     * Fetch and return the style group given a section id.
     *
     * @param number $id
     *  The id of the section
     * @retval array
     *  An array of style group items where each item has the following keys:
     *   - id:          The id of the style group.
     *   - name:        The name of the style group.
     *   - description: The description of the style group.
     */
    public function get_style_group($id)
    {
        $sql = "SELECT sg.* FROM styleGroup AS sg
            LEFT JOIN styles AS s ON s.id_group = sg.id
            LEFT JOIN sections AS sct ON sct.id_styles = s.id
            WHERE sct.id = :id";
        return $this->db->query_db_first($sql,
            array("id" => $id));
    }

    /**
     * Fetch and return all style groups from the database.
     *
     * @retval array
     *  An array of style group items where each item has the following keys:
     *   - id:          The id of the style group.
     *   - name:        The name of the style group.
     *   - description: The description of the style group.
     */
    public function get_style_groups()
    {
        $sql = "SELECT id, name, description FROM styleGroup
            WHERE id <> 1 ORDER BY position";
        return $this->db->query_db($sql);
    }

    /**
     * Fetch and return styles of a specific group from the database. If no
     * group is id provided, all styles except internal styles are returned.
     *
     * @param int $id_group
     *  The id of a style group.
     * @retval array
     *  The resulting db array where each item has the following keys:
     *   - value:   The id of the style.
     *   - text:    The name of the style.
     */
    public function get_style_list($id_group = null)
    {
        if($id_group == null)
        {
            $id_group = 1;
            $rel = "<>";
        }
        else
            $rel = "=";
        $sql = "SELECT id AS value, name AS text, description FROM styles
            WHERE id_group $rel :id ORDER BY name";
        return $this->db->query_db($sql, array(":id" => $id_group));
    }

    /**
     * Get all section items that build up the section path.
     *
     * @retval array
     *  A non-hiearrchical list of section itmes (see CmsModel::add_list_item).
     */
    public function get_section_path()
    {
        return $this->section_path;
    }

    /**
     * Returns an array of all section that are unassigned to any page and not
     * owned by anybody or ownde by the current user
     *
     * @retval array
     *  A list of section items (see CmsModel::add_list_item).
     */
    public function get_unassigned_sections()
    {
        return $this->all_unassigned_sections;
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
     * @param int position
     * The position where the section should be inserted. If not set we assign the last position
     * @retval int
     *  The id of the newly created section or false on failure.
     */
    public function insert_new_section($name, $id_style, $relation, $position = null)
    {
        $res = true;
        if(!$this->acl->has_access_update($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        $new_id = $this->db->insert("sections", array(
            "name" => $name,
            "id_styles" => $id_style
        ));
        if(!$new_id) return false;
        $res &= $this->insert_section_link($new_id, $relation, $position);
        if($res) {
            return $new_id;
        }

        return false;
    }

    /**
     * Associate a section to another section, a page, or a navigation list.
     *
     * @param int $id
     *  The id of the section to create the link to.
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     * @param int position
     * The position where the section should be inserted. If not set we assign the last position
     * @retval bool
     *  True if the insert operation is successful, false otherwise.
     */
    public function insert_section_link($id, $relation, $position = null)
    {
        if(!$this->acl->has_access_update($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        if($relation == RELATION_PAGE_CHILDREN)
        {
            $sections = $this->db->fetch_page_sections_by_id($this->id_page);
            $res = $this->db->insert("pages_sections", array(
                "id_pages" => $this->get_active_page_id(),
                "id_sections" => $id,
                "position" => $position ? $position : $this->get_last_position($sections)
            ));
            $this->adjust_hierarchy($this->get_active_page_id(), $relation);
            return $res;
        }
        else if($relation == RELATION_SECTION_CHILDREN)
        {
            $sections = $this->db->fetch_section_children(
                $this->get_active_section_id());
            $res = $this->db->insert("sections_hierarchy", array(
                "parent" => $this->get_active_section_id(),
                "child" => $id,
                "position" => $position ? $position : $this->get_last_position($sections)
            ));
            $this->adjust_hierarchy($this->get_active_section_id(), $relation);
            return $res;
        }
        else if($relation == RELATION_PAGE_NAV || $relation == RELATION_SECTION_NAV)
        {
            if($relation == RELATION_PAGE_NAV)
                $id_parent = $this->page_info["id_navigation_section"];
            else if($relation == RELATION_SECTION_NAV)
                $id_parent = $this->get_active_section_id();
            $sections = $this->db->fetch_nav_children($id_parent);
            return $this->db->insert("sections_navigation", array(
                "parent" => $id_parent,
                "child" => $id,
                "id_pages" => $this->id_page,
                "position" => $position ? $position : $this->get_last_position($sections)
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
        if (!$this->acl->has_access_update(
            $_SESSION['id_user'],
            $this->get_active_page_id()
        )) return false;
        if ($relation == RELATION_PAGE_CHILDREN) {
            $res = $this->db->remove_by_ids("pages_sections", array(
                "id_pages" => $this->id_page,
                "id_sections" => $id_section
            ));
            $this->adjust_hierarchy($this->id_page, $relation);
            return $res;
        } else if ($relation == RELATION_SECTION_CHILDREN) {
            $res = $this->db->remove_by_ids("sections_hierarchy", array(
                "parent" => $this->get_active_section_id(),
                "child" => $id_section
            ));
            $this->adjust_hierarchy($this->get_active_section_id(), $relation);
            return $res;
        } else if ($relation == RELATION_PAGE_NAV || $relation == RELATION_SECTION_NAV) {
            if ($relation == RELATION_PAGE_NAV)
            $id_parent = $this->page_info["id_navigation_section"];
            else if ($relation == RELATION_SECTION_NAV)
            $id_parent = $this->get_active_section_id();
            return $this->db->remove_by_ids("sections_navigation", array(
                "parent" => $id_parent,
                "child" => $id_section
            ));
        }
    }

    /**
     * Delete all unassigned sections and their children recursively
     * @retval boolean
     */
    public function delete_all_unassigned_sections()
    {
        try {
            $this->db->begin_transaction();
            $all_unassigned_sections = $this->fetch_unassigned_sections();
            while(count($all_unassigned_sections) > 0){
                // delete all sections with their children
                if($this->delete_unassigned_sections($all_unassigned_sections) === false){
                    $this->db->rollback();
                    return false;    
                }
                $all_unassigned_sections = $this->fetch_unassigned_sections();
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Delete all unassinged sections without their children, only the sections
     * @param array $all_unassigned_sections all sections, array by ids
     * @retval boolean
     */
    public function delete_unassigned_sections($all_unassigned_sections)
    {        
        foreach ($all_unassigned_sections as $key => $value) {
            if ($this->delete_section($key) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Set the current page acl mode.
     *
     * @param string $mode
     *  The page acl mode to set.
     */
    public function set_mode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Update the database, given the field data from one field.
     *
     * @param int $id
     *  The id of the field to update.
     * @param int $id_language
     *  The id of the language of the field to update.
     * @param int $id_gender
     *  The target gender id of the content.
     * @param string $content
     *  The content of the field to be updated.
     * @param string $relation
     *  The database relation to know whether the link targets the navigation
     *  or children list and whether the parent is a page or a section.
     * @retval bool
     *  True on success, false otherwise.
     */
    public function update_db($id, $id_language, $id_gender, $content, $relation)
    {
        if(!$this->acl->has_access_update($_SESSION['id_user'],
            $this->get_active_page_id())) return false;
        if($relation == RELATION_PAGE_FIELD)
            return $this->update_page_fields_db($id, $id_language, $content);        
        else if($relation == RELATION_SECTION_FIELD)
            return $this->update_section_fields_db($id, $id_language, $id_gender,
                $content);
        else if($relation == RELATION_SECTION_CHILDREN)
            return $this->update_section_children_order_db(
                $this->get_active_section_id(), $content);
        else if($relation == RELATION_PAGE_CHILDREN)
            return $this->update_page_children_order_db(
                $this->id_page, $content);
        else if($relation == RELATION_PAGE_NAV)
            return $this->update_nav_order_db(
                $this->page_info['id_navigation_section'], $content);
        else if($relation == RELATION_SECTION_NAV)
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
        $this->update_page_hierarchy();
        $this->update_page_sections();
        // $this->set_all_accessible_sections();
        $this->set_all_reference_sections();
        $this->all_unassigned_sections = $this->fetch_unassigned_sections();
    }

    /**
     * This function updates the CmsModel::page_sections property.
     */
    public function update_page_sections()
    {
        $this->page_sections_static = $this->fetch_page_sections();
        $this->page_sections_nav = $this->fetch_page_sections_nav();
        $this->page_sections = array_merge($this->page_sections_static,
            $this->page_sections_nav);
    }

    /**
     * This function updates the CmsModel::page_hierarchy property.
     */
    public function update_page_hierarchy()
    {
        $this->page_hierarchy = $this->fetch_page_hierarchy();
    }

    /**
     * Update the order of pages.
     *
     * @param array $order
     *  An indexed list of values where the index is the current position and
     *  the value the new poition number.
     * @param int $id
     *  The id of the parent page. If null order the root pages.
     * @retval bool
     *  True if the update operation is successful, false otherwise.
     */
    public function update_page_order($order, $id = null)
    {
        $children = $this->get_pages_header($id);
        $res = true;
        foreach($children as $index => $child)
        {
            $res &= $this->db->update_by_ids("pages",
                array("nav_position" => $order[$index]),
                array("id" => $child['id'])
            );
        }
        return $res;
    }

    /**
     * This function allows to update some model properties only when needed
     * for select and update opertaions. This allows to update the properties
     * after the controller modified the content.
     */
    public function update_select_properties()
    {
        $this->update_page_hierarchy();
        $this->navigation_hierarchy = $this->fetch_navigation_hierarchy();
        $this->update_page_sections();

        // prepare section path array
        $this->set_section_path($this->page_sections,
            $this->get_active_section_id());
        if($this->is_navigation())
            array_pop($this->section_path);
        $this->set_section_path($this->navigation_hierarchy,
            $this->get_active_root_section_id());
        $this->set_section_path($this->page_hierarchy,
            $this->get_active_page_id());
        $this->section_path = array_reverse($this->section_path);
        $this->section_path[count($this->section_path)-1]["url"] = null;
    }

    /**
     * This function allows to update some model properties only when needed
     * for delete opertaions. This allows to update the properties after the
     * controller modified the content.
     */
    public function update_delete_properties()
    {
        $this->update_page_sections();
        $this->navigation_hierarchy = $this->fetch_navigation_hierarchy();
    }

    public function get_db(){
        return $this->db;
    }

    /**
     * Return a list of group items the user can grant access to the page.The
     * list is prepared such that it can be passed to a select form. 
     * Groups: Admin, therapist and subject are not in the list as they recieve the access by default
     *
     * @retval array
     *  An array of group items where each item has the following keys:
     *   'value':   The id of the group.
     *   'text':    The name of the group.
     */
    public function get_groups_grant_access_to_page($uid)
    {
        $groups = array();
        $sql = "SELECT g.id AS value, g.name AS text FROM groups AS g
            LEFT JOIN users_groups AS ug ON ug.id_groups = g.id AND ug.id_users = :uid
            WHERE ug.id_users IS NULL
            ORDER BY g.name";
        $groups_db = $this->db->query_db($sql, array(":uid" => $uid));
        foreach ($groups_db as $group) {
                $groups[] = $group;
        }
        return $groups;
    }

    /**
     * Update a field of the current section.
     *
     * @param int $id
     *  The id of the field to update.
     * @param int $id_language
     *  The id of the language of the field.
     * @param int $id_gender
     *  The id of the target gender of the field.
     * @param string $content
     *  The new content.
     * @retval bool
     *  True if the update operation is successful, false otherwise.
     */
    public function update_section_fields_db($id, $id_language, $id_gender,
        $content, $id_section = null)
    {        
        if ($id_section === null && isset($_POST['id_section'])) {
            // if the id is coming in the post parameter set it
            $id_section = $_POST['id_section'];
        }
        if($id_section === null)
            $id_section = $this->get_active_section_id();
        if($id_section == null && $this->is_navigation())
            $id_section = $this->page_info['id_navigation_section'];        
        $update = array(
            "content" => $content
        );
        $insert = array(
            "content" => $content,
            "id_fields" => $id,
            "id_languages" => $id_language,
            "id_sections" => $id_section,
            "id_genders" => $id_gender,
        );
        return $this->db->insert("sections_fields_translation", $insert, $update);
    }    

    /**
     * Update the page table
     *
     * @param array $page_fields
     *  It contains the column and the value that should be assigned
     * @param string $position
     *  The position string if the new page should appear in the navbar, null otherwise.
     * @retval bool
     *  True if the update operation is successful, false otherwise.
     */
    public function update_page($page_fields, $position = null)
    {        
        $res = $this->db->update_by_ids(
            "pages",
            $page_fields,
            array("id" => $this->id_page)
        );
        if($position){
            $this->update_page_order($position, $this->get_parent_page_id($this->id_page));
        }
        return $res;
    }

    /**
     * Update the section table
     *
     * @param array $section_fields
     * it contains the column and the value that should be assigned
     * @retval bool
     *  True if the update operation is successful, false otherwise.
     */
    public function update_section($section_fields)
    {
        if(isset($section_fields['section_name'])){
            $section_fields['name'] = $section_fields['section_name']; // use the real column name form the DB, it was used section_name otherwise it was overwritten by styles which have field `name`
            unset($section_fields['section_name']);
        }
        return $this->db->update_by_ids(
            "sections",
            $section_fields,
            array("id" => isset($_POST['id_section']) ? $_POST['id_section'] : $this->id_root_section)
            // array("id" => ($this->id_root_section ? $this->id_root_section : (isset($_POST['id_section']) ? $_POST['id_section'] : $this->id_root_section)))
        );
    }

    /**
     * Get the id of the root section
     * @return int 
     * Return the id of the root section
     */
    public function get_id_root_section()
    {
        return $this->id_root_section;
    }

    /**
     * Get the parent page id if there is one
     * @param int $page_id 
     * the page id that we want to get parent
     * @retval int or null
     * return the parent id or null
     */
    public function get_parent_page_id($page_id)
    {
        $sql = "SELECT parent FROM pages WHERE id = :page_id";
        return $this->db->query_db_first($sql, array("page_id" => $page_id))['parent'];
    }

    /**
     * Get all styles
     * @return array
     * Return all styles' names in array
     */
    public function get_styles()
    {
        $sql = "SELECT name FROM styles;";
        return $this->db->query_db($sql, array());
    }
}
?>

