<?php
/**
 * This class is used to prepare all data related to the sessionsNav component
 * such that the data can easily be displayed in the view of the component.
 */
class SessionsNavModel
{
    /* Private Properties *****************************************************/

    private $db;
    private $sessions;
    private $items;
    private $next_id;
    private $last_id;
    private $current_id;
    private $current_idx;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($db, $current_id)
    {
        $this->db = $db;
        $this->current_id = $current_id;
        $container = $this->db->fetch_page_sections("sessions");
        $this->items = array();
        $this->sessions = $this->fetch_children(intval($container[0]['id']));
        $this->set_current_index($current_id);
    }

    /* Private Methods ********************************************************/

    /**
     * Fetches all session items from the database and assembles them
     * hierarchically in an array.
     *
     * @return array
     *  A hierarchical array with the fields
     *   'id': the session id
     *   'title': the session title
     *   'children': the children of this session
     */
    private function fetch_children($id_section)
    {
        $children = array();
        $ids = $this->db->fetch_section_children($id_section);
        foreach($ids as $id)
        {
            $fields = array();
            $db_fields = $this->db->fetch_section_fields($id['id']);
            foreach($db_fields as $field)
                $fields[$field['name']] = $field['content'];
            $fields['id'] = $id['id'];
            array_push($this->items, $fields);
            $fields['children'] = $this->fetch_children($id['id']);
            array_push($children, $fields);
        }
        return $children;
    }

    /**
     * Given the current id, set the current index of the flattened item list.
     *
     * @param int $id
     *  The current secssion id.
     */
    private function set_current_index($id)
    {
        foreach($this->items as $index => $item)
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
     * Gets the title of the session page which is used as a prefix for each
     * session title.
     *
     * @return string
     *  The generic session title.
     */
    public function get_item_label()
    {
        $page_info = $this->db->fetch_page_info("session");
        return $page_info['title'];
    }

    /**
     * Gets the hierarchical assembled session items.
     *
     * @return array
     *  A hierarchical array. See SessionsNavModel::fetch_children($id_section).
     */
    public function get_children()
    {
        return $this->sessions;
    }

    /**
     * Gets the current session id.
     *
     * @retval int
     *  The current session id.
     */
    public function get_current_session_id()
    {
        return $this->current_id;
    }

    /**
     * Gets the next session id.
     *
     * @retval int
     *  The next sessions id.
     */
    public function get_next_session_id()
    {
        $next_idx = $this->current_idx + 1;
        if($next_idx < count($this->items))
            return intval($this->items[$next_idx]['id']);
        else
            false;
    }

    /**
     * Gets the previous session id.
     *
     * @retval int
     *  The previous sessions id.
     */
    public function get_previous_session_id()
    {
        $prev_idx = $this->current_idx - 1;
        if($prev_idx >= 0)
            return intval($this->items[$prev_idx]['id']);
        else
            false;
    }

    /**
     * Gets the number of sessions of the naviagetion.
     *
     * @retval int
     *  The number of sessions.
     */
    public function get_session_count()
    {
        return count($this->sessions);
    }
}
?>
