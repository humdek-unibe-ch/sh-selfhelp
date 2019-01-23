<?php
require_once __DIR__ . "/ChatModel.php";
/**
 * This class is a specified chat model for the role therapist.
 */
class ChatModelTherapist extends ChatModel
{
    /* Private Properties *****************************************************/

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
    public function __construct($services, $id, $gid, $uid)
    {
        parent::__construct($services, $id, $gid, $uid);
    }

    /* Protected Methods ******************************************************/

    /**
     * In the therapist role, the active user is the user id passed via GET
     * parameters.
     *
     * @retval int
     *  The id of the selected user.
     */
    protected function get_active_user()
    {
        return $this->uid;
    }

    /* Public Methods *********************************************************/

    /**
     * Get the user name of the selected user.
     *
     * @retval string
     *  The name of the selected user.
     */
    public function get_selected_user_name()
    {
        $sql = "SELECT name FROM users WHERE id = :uid";
        $name = $this->db->query_db_first($sql, array(":uid" => $this->uid));
        if($name)
            return $name["name"];
        else
            return "";
    }

    /**
     * Get all subjects in a the selected room which have written a msg.
     *
     * @retval array
     *  The database result with the following keys:
     *   'id':      The user id of the subject.
     *   'name':    The name of the subject.
     */
    public function get_subjects()
    {
        $sql = "SELECT DISTINCT u.id, u.name FROM users AS u
            LEFT JOIN chat AS c ON c.id_snd = u.id
            LEFT JOIN users_groups AS ug ON ug.id_users = u.id
            WHERE c.id_rcv_grp = :rid AND ug.id_groups = :gid";
        return $this->db->query_db($sql, array(
            ":gid" => SUBJECT_GROUP_ID,
            ":rid" => $this->gid,
        ));
    }

    /**
     * Get the url of a given chat subject.
     *
     * @param int $uid
     *  The id of a chat subject.
     * @retval string
     *  The url to a chat subject.
     */
    public function get_subject_url($uid)
    {
        return $this->get_link_url("contact",
            array("gid" => $this->gid, "uid" => $uid));
    }

    /**
     * Get the number of new subject messages.
     *
     * @param int $id
     *  The id of the chat room the check for new messages.
     * @retval int
     *  The number of new messages in a chat room.
     */
    public function get_subject_message_count($id)
    {
        $sql = "SELECT COUNT(c.id) AS count FROM chat AS c
            WHERE c.is_new = '1' AND c.id_rcv_grp = :gid AND (c.id_snd = :uid
                OR (c.id_rcv = :uid AND c.id_snd != :me))";
        $res = $this->db->query_db_first($sql, array(
            ':uid' => $id,
            ':me' => $_SESSION['id_user'],
            ':gid' => $this->gid,
        ));
        if($res)
            return intval($res['count']);
        return 0;
    }

    /**
     * Get the number of new room messages. With the role therapist this are
     * all new messages that were sent to the indicated group (excluding the
     * ones sent by the current user).
     *
     * @param int $id
     *  The id of the chat room the check for new messages.
     * @retval int
     *  The number of new messages in a chat room.
     */
    public function get_room_message_count($id)
    {
        $sql = "SELECT COUNT(c.id) AS count FROM chat AS c
            WHERE c.is_new = '1' AND c.id_rcv_grp = :gid AND c.id_snd != :me";
        $res = $this->db->query_db_first($sql, array(
            ':gid' => $id,
            ':me' => $_SESSION['id_user'],
        ));
        if($res)
            return intval($res['count']);
        return 0;
    }

    /**
     * Checks whether all required parameters are set.
     *
     * @retval bool
     *  True if chat is ready, false otherwise.
     */
    public function is_chat_ready()
    {
        return ($this->gid !== null && $this->uid !== null);
    }

    /**
     * Checks whether a given subject is currently selected.
     *
     * @param int $id
     *  The id of the subject to check.
     * @retval bool
     *  True if the given subject is selected, false otherwise.
     */
    public function is_subject_selected($id)
    {
        return ($id === $this->uid);
    }

    /**
     * Insert the chat item to the database. In the role of a therapist, a
     * specific user recipiant is specified as well as a recipiant chat room.
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
            "id_rcv" => $this->uid,
            "id_rcv_grp" => $this->gid,
            "content" => $msg,
            "is_new" => '1',
        ));
    }
}
?>
