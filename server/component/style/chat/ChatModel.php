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

    /* Public Properties ******************************************************/

    public $is_experimenter;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all chat related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section id of the chat wrapper.
     */
    public function __construct($services, $id, $uid)
    {
        parent::__construct($services, $id);
        $this->uid = $uid;
        $this->is_experimenter = false;
        $rel = $this->fetch_experimenter_relation();
        if($rel) $this->is_experimenter = true;
    }

    private function fetch_experimenter_relation()
    {
        $sql = "SELECT * FROM users_groups
            WHERE id_users = :uid AND id_groups = :gid";
        return $this->db->query_db_first($sql, array(
            ":uid" => $_SESSION['id_user'],
            ":gid" => EXPERIMENTER_GROUP_ID,
        ));
    }

    private function send_msg_to_user($msg, $sender, $receiver)
    {
        return $this->db->insert("chat", array(
            "id_send" => $sender,
            "id_rcv_user" => $receiver,
            "content" => $msg
        ));
    }

    private function send_msg_to_group($msg, $sender, $receiver)
    {
        return $this->db->insert("chat", array(
            "id_send" => $sender,
            "id_rcv_group" => $receiver,
            "content" => $msg
        ));
    }

    /* Public Methods *********************************************************/

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

    public function get_subjects()
    {
        $sql = "SELECT u.id, u.name FROM users AS u
            LEFT JOIN users_groups AS ug ON ug.id_users = u.id
            WHERE ug.id_groups = :gid AND name IS NOT NULL";
        return $this->db->query_db($sql, array(":gid" => SUBJECT_GROUP_ID));
    }

    public function send_chat_msg($msg)
    {
        return $this->db->insert("chat", array(
            "id_snd" => $_SESSION['id_user'],
            "id_rcv" => $this->uid,
            "content" => $msg
        ));
    }

    public function is_selected_user($uid)
    {
        return ($this->uid === $uid);
    }

    public function is_chat_ready()
    {
        return (($this->is_experimenter && $this->uid != null)
            || (!$this->is_experimenter && $this->uid == null));
    }
}
?>
