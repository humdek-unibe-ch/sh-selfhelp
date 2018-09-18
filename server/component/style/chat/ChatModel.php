<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the chat component such
 * that the data can easily be displayed in the view of the component.
 */
class ChatModel extends StyleModel
{
    /* Private Properties *****************************************************/

    private $uid;
    private $is_experimenter;
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
     * @param int $uid
     *  The id of the user to communicate with
     */
    public function __construct($services, $id, $uid)
    {
        parent::__construct($services, $id);
        $this->uid = $uid;
        $this->is_experimenter = false;
        $this->subjects = $this->fetch_subjects();
        if($this->check_experimenter_relation($_SESSION['id_user']))
            $this->is_experimenter = true;
    }

    /**
     * Check whether a user is part of the experimenter group.
     *
     * @param int $uid
     *  The id of the user to check.
     * @retval array
     *  True if the user is part of the experimneter group, false otherwise.
     */
    private function check_experimenter_relation($uid)
    {
        $sql = "SELECT * FROM users_groups
            WHERE id_users = :uid AND id_groups = :gid";
        $res = $this->db->query_db_first($sql, array(
            ":uid" => $uid,
            ":gid" => EXPERIMENTER_GROUP_ID,
        ));
        if($res) return true;
        else return false;
    }

    /**
     * Get all subjects of thet experiment.
     *
     * @retval array
     *  The database result with the following keys:
     *   'id':      The user id of the subject.
     *   'name':    The name of the subject.
     */
    private function fetch_subjects()
    {
        $sql = "SELECT u.id, u.name FROM users AS u
            LEFT JOIN users_groups AS ug ON ug.id_users = u.id
            WHERE ug.id_groups = :gid AND name IS NOT NULL";
        return $this->db->query_db($sql, array(":gid" => SUBJECT_GROUP_ID));
    }

    /* Public Methods *********************************************************/

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
        if(!$this->is_chat_ready()) return array();
        $sql = "SELECT usnd.id AS uid, usnd.name AS name, chat.content AS msg,
            chat.timestamp
            FROM chat
            LEFT JOIN users AS usnd ON usnd.id = chat.id_snd
            LEFT JOIN users AS urcv ON urcv.id = chat.id_rcv
            WHERE usnd.id = :uid OR urcv.id = :uid
            ORDER BY chat.timestamp";
        if($this->is_experimenter)
            $uid = $this->uid;
        else
            $uid = $_SESSION['id_user'];
        return $this->db->query_db($sql, array(":uid" => $uid));
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
            if($this->is_selected_user(intval($subject['id'])))
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
        return (($this->is_experimenter && $this->uid != null)
            || (!$this->is_experimenter && $this->uid == null));
    }

    /**
     * Checks whether the current user belongs to the experimenter group or not.
     *
     * @retval bool
     *  True if the current user is an experimenter, false otherwise.
     */
    public function is_current_user_experimenter()
    {
        return $this->is_experimenter;
    }

    /**
     * Checks whether a user is the selected user.
     *
     * @param int $uid
     *  The user id to be checked.
     * @retval bool
     *  True if the user is the selected user, false otherwise.
     */
    public function is_selected_user($uid)
    {
        return ($this->uid === $uid);
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
            "content" => $msg
        ));
    }
}
?>
