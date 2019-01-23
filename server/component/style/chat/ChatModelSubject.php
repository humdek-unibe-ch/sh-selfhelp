<?php
require_once __DIR__ . "/ChatModel.php";
/**
 * This class is used to prepare all data related to the chat component such
 * that the data can easily be displayed in the view of the component.
 */
class ChatModelSubject extends ChatModel
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
     */
    public function __construct($services, $id, $gid)
    {
        parent::__construct($services, $id, $gid);
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
            ":uid" => $_SESSION['id_user'],
            ":rid" => $this->gid,
        ));
    }

    public function is_chat_ready()
    {
        return ($this->gid !== null);
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
