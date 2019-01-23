<?php
require_once __DIR__ . "/ChatModel.php";
/**
 * This class is used to prepare all data related to the chat component such
 * that the data can easily be displayed in the view of the component.
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
     *  The group id to communicate with
     * @param int $uid
     *  The user id to communicate with
     */
    public function __construct($services, $id, $gid, $uid)
    {
        parent::__construct($services, $id, $gid, $uid);
    }

    /* Public Methods *********************************************************/

    /**
     *
     */
    public function get_chat_items_spec()
    {
        $sql = "SELECT c.id AS cid, u.id AS uid, u.name AS name,
            c.content AS msg, c.timestamp
            FROM chat AS c
            LEFT JOIN users AS u ON u.id = c.id_snd
            WHERE c.id_rcv_grp = :rid AND (c.id_snd = :uid OR c.id_rcv = :uid)
            ORDER BY c.timestamp";
        return $this->db->query_db($sql, array(
            ":uid" => $this->uid,
            ":rid" => $this->gid,
        ));
    }

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

    public function get_subject_url($uid)
    {
        return $this->get_link_url("contact",
            array("gid" => $this->gid, "uid" => $uid));
    }

    public function is_subject_selected($id)
    {
        return ($id === $this->uid);
    }

    public function is_chat_ready()
    {
        return ($this->gid !== null && $this->uid !== null);
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
            "id_rcv" => $this->uid,
            "id_rcv_grp" => $this->gid,
            "content" => $msg
        ));
    }
}
?>
