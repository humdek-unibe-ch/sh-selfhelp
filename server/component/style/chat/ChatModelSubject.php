<?php
require_once __DIR__ . "/ChatModel.php";
/**
 * This class is used to prepare all data related to the chat component such
 * that the data can easily be displayed in the view of the component.
 */
class ChatModelSubject extends ChatModel
{
    /* Private Properties *****************************************************/

    /**
     * The id of the group to communicate with.
     */
    private $gid = null;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all chat related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section id of the chat wrapper.
     * @param int $aid
     *  The id of the user to communicate with.
     */
    public function __construct($services, $id, $aid)
    {
        parent::__construct($services, $id);
        if(count($this->rooms) === 0)
            $this->gid = GLOBAL_CHAT_ROOM_ID;
        else if(count($this->rooms) === 1)
            $this->gid = intval($this->rooms[0]['id']);
        else
            $this->gid = $aid;
    }

    /* Public Methods *********************************************************/

    /**
     *
     */
    public function get_chat_items_spec()
    {
        $sql = "SELECT chat.id AS cid, usnd.id AS uid, usnd.name AS name,
            chat.content AS msg, chat.timestamp
            FROM chat
            LEFT JOIN users AS usnd ON usnd.id = chat.id_snd
            LEFT JOIN users AS urcv ON urcv.id = chat.id_rcv
            LEFT JOIN chatRoom_users AS crusnd ON crusnd.id_users = usnd.id
            LEFT JOIN chatRoom_users AS crurcv ON crurcv.id_users = usnd.id
            WHERE (usnd.id = :uid AND (crusnd.id_chatRoom = :gid OR crusnd.id_chatRoom IS NULL))
                OR (urcv.id = :uid AND (crurcv.id_chatRoom = :gid OR crurcv.id_chatRoom IS NULL))
            ORDER BY chat.timestamp";
        return $this->db->query_db($sql, array(
            ":uid" => $_SESSION['id_user'],
            ":gid" => $this->gid,
        ));
    }

    /**
     * Checks whether all parameters are set correctly.
     *
     * @retval bool
     *  True if all is in order, false if some parameters are inconsistent.
     */
    public function is_chat_ready()
    {
        return ($this->gid !== null);
    }

    /**
     * Checks whether an id is the selected id.
     *
     * @param int $aid
     *  The id to be checked.
     * @retval bool
     *  True if the is is the selected, false otherwise.
     */
    public function is_selected_id($aid)
    {
        return ($this->gid === $aid);
    }

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
    public function send_chat_msg($msg)
    {
        return $this->db->insert("chat", array(
            "id_snd" => $_SESSION['id_user'],
            "id_rcv_grp" => $this->gid,
            "content" => $msg
        ));
    }
}
?>
