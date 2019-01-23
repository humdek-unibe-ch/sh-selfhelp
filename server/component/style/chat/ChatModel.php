<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the chat component such
 * that the data can easily be displayed in the view of the component.
 */
abstract class ChatModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The active group id to communicate with.
     */
    protected $gid = null;

    /**
     * The active user id to communicate with.
     */
    protected $uid = null;

    /**
     * The list of rooms.
     */
    protected $rooms;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all chat related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section id of the chat wrapper.
     * @param int $gid
     *  The group id to communicate with
     * @param int $uid
     *  The user id to communicate with
     */
    public function __construct($services, $id, $gid, $uid=null)
    {
        parent::__construct($services, $id);
        $this->gid = $gid ?? GLOBAL_CHAT_ROOM_ID;
        $this->uid = $uid;
        $this->rooms = $this->fetch_rooms();
    }

    /**
     * Get all rooms the current user is assigned to.
     *
     * @retval array
     *  The database result with the following keys:
     *   'id':      The room id.
     *   'name':    The name of the room.
     */
    private function fetch_rooms()
    {
        $sql = "SELECT r.id, r.name FROM chatRoom AS r
            LEFT JOIN chatRoom_users AS cu ON cu.id_chatRoom = r.id
            WHERE cu.id_users = :uid";
        return $this->db->query_db($sql, array(":uid" => $_SESSION['id_user']));
    }

    /* Public Methods *********************************************************/

    /**
     * Checks whether the current user is allowed to add and remove users from
     * chat rooms.
     *
     * @retval bool
     *  True if the current user can administrate the chat, false otherwise.
     */
    public function can_administrate_chat()
    {
        return $this->acl->has_access_update($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("contact"));
    }

    /**
     * Checks whether the current user is allowed to create new chat rooms.
     *
     * @retval bool
     *  True if the current user can create new chat rooms, false otherwise.
     */
    public function can_create_new_room()
    {
        return $this->acl->has_access_insert($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("contact"));
    }

    /**
     * Get the chat itmes. If the current user is an experimenter all chat items
     * related to a selected user are returned. If the current user is not an
     * experimenter all chat items related to the current user are returned.
     *
     * @retval array
     *  The database result with the following keys:
     *   'uid':         The id of the user who sent the chat item.
     *   'name':        The name of the user who sent the chat item.
     *   'msg':         The content of the chat item.
     *   'timestamp':   The timestamp of when the chat item was sent.
     */
    public function get_chat_items()
    {
        /* if(!$this->is_chat_ready()) return array(); */
        $items = $this->get_chat_items_spec();
        $ids = array();
        foreach($items as $item)
            $ids[] = $item['cid'];
        if(count($ids) > 0)
        {
            $sql = 'UPDATE chat SET is_new = 0 WHERE id in (' . implode(',', $ids) . ')';
            $this->db->execute_db($sql);
        }
        return $items;
    }

    /**
     *
     */
    abstract public function get_chat_items_spec();

    /**
     * Get the list of rooms.
     *
     * @retval array
     *  The result from the db query see ChatModel::fetch_rooms().
     */
    public function get_rooms()
    {
        return $this->rooms;
    }

    public function is_room_selected($id)
    {
        return ($id === $this->gid);
    }

    abstract public function is_chat_ready();

    /**
     * Insert the chat item to the database. If the current user is an
     * experimenter the chat item is sent to the selected user. If the current
     * user is not an experimenter, the chat item is sent to the experimenter
     * group (i.e. no recipiant is specified).
     *
     * @param string $msg
     *  The chat item content.
     * @retval int
     *  The id of the chat item on success, false otherwise.
     */
    abstract public function send_chat_msg($msg);
}
?>
