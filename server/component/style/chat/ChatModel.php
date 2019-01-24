<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the chat component such
 * that the data can easily be displayed in the view of the component.
 *
 * This is a base class and needs to be extended by a role (either subject or
 * therapist).
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
     *  The chat room id to communicate with
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

    /* Private Methodes *******************************************************/

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

    /* Abstract Protected Methodes ********************************************/

    /**
     * Get the active user (either defined vie GET param or the session)
     *
     * @retval int
     *  The active user id.
     */
    abstract protected function get_active_user();

    /* Abstract Public Methodes ***********************************************/

    /**
     * Get the number of neww room messages. This dependes on the role.
     *
     * @param int $id
     *  The id of the chat room the check for new messages.
     * @retval int
     *  The number of new messages in a chat room.
     */
    abstract public function get_room_message_count($id);

    /**
     * Checks whether all required parameters are set. This depends on the role.
     *
     * @retval bool
     *  True if chat is ready, false otherwise.
     */
    abstract public function is_chat_ready();


    /* Public Methods *********************************************************/

    /**
     * Get the chat itmes. If the current user is an therapist all chat items
     * related to a selected user are returned. If the current user is not an
     * experimenter all chat items related to the current user are returned.
     *
     * @retval array
     *  The database result with the following keys:
     *   - 'uid':         The id of the user who sent the chat item.
     *   - 'name':        The name of the user who sent the chat item.
     *   - 'msg':         The content of the chat item.
     *   - 'timestamp':   The timestamp of when the chat item was sent.
     *   - 'is_new':      Indicates whether the message is new.
     */
    public function get_chat_items()
    {
        $sql = "SELECT c.is_new, c.id AS cid, u.id AS uid, u.name AS name,
            c.content AS msg, c.timestamp, c.is_new
            FROM chat AS c
            LEFT JOIN users AS u ON u.id = c.id_snd
            WHERE c.id_rcv_grp = :rid AND (c.id_snd = :uid OR c.id_rcv = :uid)
            ORDER BY c.timestamp";
        $items = $this->db->query_db($sql, array(
            ":uid" => $this->get_active_user(),
            ":rid" => $this->gid,
        ));
        $ids = array();
        foreach($items as $item)
        {
            if($item['uid'] == $_SESSION['id_user'])
                continue;
            $ids[] = $item['cid'];
        }
        if(count($ids) > 0)
        {
            $sql = 'UPDATE chat SET is_new = 0 WHERE id in (' . implode(',', $ids) . ')';
            $this->db->execute_db($sql);
        }
        return $items;
    }

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

    /**
     * Checks whether the current user is in the active group.
     *
     * @retval bool
     *  True if the user is in the active group, false otherwise.
     */
    public function is_current_user_in_active_group()
    {
        $sql = "SELECT * FROM chatRoom_users
            WHERE id_chatRoom = :rid AND id_users = :uid";
        $res = $this->db->query_db($sql, array(
            ":rid" => $this->gid,
            ":uid" => $_SESSION['id_user'],
        ));
        return ($res || $this->gid === GLOBAL_CHAT_ROOM_ID);
    }

    /**
     * Checks whether a given room is currently selected.
     *
     * @param int $id
     *  The id of the chat room to check.
     * @retval bool
     *  True if the given room is selected, false otherwise.
     */
    public function is_room_selected($id)
    {
        return ($id === $this->gid);
    }

    /**
     * Insert the chat item to the database. This method depends on the role.
     *
     * If the current user is an
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
