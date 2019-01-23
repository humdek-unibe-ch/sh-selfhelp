<?php
require_once __DIR__ . "/ChatModel.php";
/**
 * This class is used to prepare all data related to the chat component such
 * that the data can easily be displayed in the view of the component.
 */
class ChatModelTherapist extends ChatModel
{
    /* Private Properties *****************************************************/

    /**
     * The id of the user to communicate with.
     */
    private $uid = null;

    /**
     * The list of subjects.
     */
    private $subjects;

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
        $this->uid = $aid;
        $this->subjects = $this->fetch_subjects();
    }

    /**
     * Get all subjects of room.
     *
     * @param int $rid
     *  The id of the room.
     * @retval array
     *  The database result with the following keys:
     *   'id':      The user id of the subject.
     *   'name':    The name of the subject.
     */
    private function fetch_subjects_in_room($rid)
    {
        $sql = "SELECT u.id, u.name FROM users AS u
            LEFT JOIN chatRoom_users AS cu ON cu.id_users = u.id
            LEFT JOIN users_groups AS ug ON ug.id_users = u.id
            WHERE ug.id_groups = :gid AND name IS NOT NULL
            AND cu.id_chatRoom = :rid";
        return $this->db->query_db($sql, array(
            ":gid" => SUBJECT_GROUP_ID,
            ":rid" => $rid,
        ));
    }

    /**
     * Get all subjects of that experiment that are in the same rooms as the
     * therapist.
     *
     * @retval array
     *  The database result with the following keys:
     *   'id':      The user id of the subject.
     *   'name':    The name of the subject.
     */
    private function fetch_subjects()
    {
        $subjects = array();
        foreach($this->rooms as $room)
        {
            $subjects_db = $this->fetch_subjects_in_room($room['id']);
            foreach($subjects_db as $subject)
                $subjects[intval($subject['id'])] = $subject;
        }
        return $subjects;
    }

    /* Public Methods *********************************************************/

    /**
     *
     */
    public function get_chat_items_spec_room($rid)
    {
        $sql = "SELECT chat.id AS cid, usnd.id AS uid, usnd.name AS name,
            chat.content AS msg, chat.timestamp
            FROM chat
            LEFT JOIN users AS usnd ON usnd.id = chat.id_snd
            LEFT JOIN users AS urcv ON urcv.id = chat.id_rcv
            WHERE (usnd.id = :uid_subj AND chat.id_rcv_grp = :gid)
                OR urcv.id = :uid_subj
            ORDER BY chat.timestamp";
        return $this->db->query_db($sql, array(
            ":uid_subj" => $this->uid,
            ":uid_me" => $_SESSION['id_user'],
            ":gid" => $rid,
        ));
    }

    /**
     *
     */
    public function get_chat_items_spec()
    {
        $msgs = array();
        $rooms = $this->rooms;
        if(count($rooms) === 0)
            $rooms[] = array('id' => GLOBAL_CHAT_ROOM_ID);

        foreach($rooms as $room)
        {
            $msgs_db = $this->get_chat_items_spec_room($room['id']);
            foreach($msgs_db as $msg)
                $msgs[intval($msg['cid'])] = $msg;
        }
        return $msgs;
    }

    /**
     * Get the user name of the selected user.
     *
     * @retval string
     *  The name of the selected user.
     */
    public function get_selected_user_name()
    {
        foreach($this->subjects as $subject)
            if($this->is_selected_id(intval($subject['id'])))
                return $subject['name'];
        return "";
    }

    /**
     * Get the list of subjects.
     *
     * @retval array
     *  The result from the db query see ChatModel::fetch_subjects().
     */
    public function get_subjects()
    {
        return $this->subjects;
    }

    /**
     * Checks whether all parameters are set correctly.
     *
     * @retval bool
     *  True if all is in order, false if some parameters are inconsistent.
     */
    public function is_chat_ready()
    {
        return ($this->uid !== null);
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
        return ($this->uid === $aid);
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
            "id_rcv" => $this->aid,
            "content" => $msg
        ));
    }
}
?>
