<?php
/**
 * This class is used to prepare all data related to the navSection component
 * such that the data can easily be displayed in the view of the component.
 */
class Navigation
{
    /* Private Properties *****************************************************/

    private $db;
    private $router;
    private $keyword;
    private $items_tree;
    private $items_list;
    private $root_id;
    private $current_id;
    private $current_idx;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $root_id
     *  The id of the root section.
     */
    public function __construct($router, $db, $keyword, $root_id)
    {
        $this->db = $db;
        $this->router = $router;
        $this->keyword = $keyword;
        $this->root_id = $root_id;
        $this->current_id = $root_id;
        $this->items_list = array();
        $this->items_tree = $this->fetch_children($root_id);
    }

    /* Private Methods ********************************************************/

    /**
     * Fetches all navigation items from the database and assembles them
     * hierarchically in an array. Further, items are added to list structure.
     *
     * @param int $id_section
     *  The root item id.
     *
     * @return array
     *  A hierarchical array with the fields
     *   'id': the section id
     *   'title': the section title
     *   'children': the children of this section
     *   'url': the target url
     */
    private function fetch_children($id_section)
    {
        $children = array();
        $sql = "SELECT child AS id FROM sections_navigation
            WHERE parent = :id
            ORDER BY position";
        $ids = $this->db->query_db($sql, array(":id" => $id_section));
        foreach($ids as $id)
        {
            $fields = array();
            $db_fields = $this->db->fetch_section_fields($id['id']);
            foreach($db_fields as $field)
                $fields[$field['name']] = $field['content'];
            $fields['id'] = intval($id['id']);
            array_push($this->items_list, $fields);
            $fields['children'] = $this->fetch_children(intval($id['id']));
            $fields['url'] = $this->router->generate($this->keyword,
                array("id" => intval($id['id'])));
            array_push($children, $fields);
        }
        return $children;
    }

    /* Public Methods *********************************************************/

    /**
     * Gets the hierarchical assembled navigation items.
     *
     * @return array
     *  A hierarchical array. See NavSectionModel::fetch_children($id_section).
     */
    public function get_navigation_items()
    {
        return $this->items_tree;
    }

    /**
     * Gets the root navigation item id.
     *
     * @retval int
     *  The root navigation item id.
     */
    public function get_root_id()
    {
        return $this->root_id;
    }

    /**
     * Gets the current navigation item id.
     *
     * @retval int
     *  The current navigation item id.
     */
    public function get_current_id()
    {
        return $this->current_id;
    }

    /**
     * Gets the next navigation item id.
     *
     * @retval int
     *  The next navigation item id or false if it does not exist.
     */
    public function get_next_id()
    {
        $next_idx = $this->current_idx + 1;
        if($next_idx < count($this->items_list))
            return intval($this->items_list[$next_idx]['id']);
        else
            false;
    }

    /**
     * Gets the previous navigation item id.
     *
     * @retval int
     *  The previous navigation item id or false if it does not exist.
     */
    public function get_previous_id()
    {
        $prev_idx = $this->current_idx - 1;
        if($prev_idx >= 0)
            return intval($this->items_list[$prev_idx]['id']);
        else
            false;
    }

    /**
     * Gets the number of root navigation items.
     *
     * @retval int
     *  The number of navigation items.
     */
    public function get_count()
    {
        return count($this->items_tree);
    }

    /**
     * Returns true if a given  section id can be found in the list of
     * sections associated to the navigation page.
     *
     * @param int $id
     *  The id to check.
     * @retval bool
     *  True if the id exists, false otherwise.
     */
    public function section_id_exists($id)
    {
        foreach($this->items_list as $item)
            if(intval($item['id']) == $id)
                return true;
        return false;
    }

    /**
     * Given the current id, set the current index of the flattened item list.
     *
     * @param int $id
     *  The current navigation item id.
     */
    public function set_current_index($id)
    {
        $this->current_id = $id;
        foreach($this->items_list as $index => $item)
        {
            if($item['id'] == $id)
            {
                $this->current_idx = $index;
                return;
            }
        }
    }
}
?>
