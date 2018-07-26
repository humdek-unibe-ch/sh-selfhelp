<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the navSection component
 * such that the data can easily be displayed in the view of the component.
 */
class NavSectionModel extends BaseModel
{
    /* Private Properties *****************************************************/

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
     * @param int $current_id
     *  The id of the current section.
     */
    public function __construct($services, $root_id, $current_id)
    {
        parent::__construct($services);
        $this->root_id = $root_id;
        $this->current_id = ($current_id == 0) ? $root_id : $current_id;
        $this->items_list = array();
        $this->items_tree = $this->fetch_children($root_id);
        $this->set_current_index($current_id);
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
            array_push($children, $fields);
        }
        return $children;
    }

    /**
     * Given the current id, set the current index of the flattened item list.
     *
     * @param int $id
     *  The current navigation item id.
     */
    private function set_current_index($id)
    {
        foreach($this->items_list as $index => $item)
        {
            if($item['id'] == $id)
            {
                $this->current_idx = $index;
                return;
            }
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Gets a navigation itme prefix if available. The prefix corresponds to
     * the title field of the navigation section.
     *
     * @return string
     *  The navigation item prefix.
     */
    public function get_item_prefix()
    {
        $db_fields = $this->db->fetch_section_fields($this->root_id);
        foreach($db_fields as $field)
            if($field['name'] == "title")
                return $field['content'];
        return "";
    }

    /**
     * Gets the hierarchical assembled navigation items.
     *
     * @return array
     *  A hierarchical array. See NavSectionModel::fetch_children($id_section).
     */
    public function get_children()
    {
        return $this->items_tree;
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
}
?>
