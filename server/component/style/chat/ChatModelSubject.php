<?php
require_once __DIR__ . "/ChatModel.php";
/**
 * This class is a specified chat model for the role subject.
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
     *  The chat room id to communicate with
     */
    public function __construct($services, $id, $gid)
    {
        parent::__construct($services, $id, $gid);
    }

    /* Protected Methods ******************************************************/

    /**
     * In the subject role, the active user is the id stored in the session.
     *
     * @retval int
     *  The id of the session user.
     */
    protected function get_active_user()
    {
        return $_SESSION['id_user'];
    }

    /* Public Methods *********************************************************/

    /**
     * Get the number of new room messages. With the role subject this are all
     * new messages that were sent to the indicated group and the current user
     * (excluding the ones sent by the current user).
     *
     * @param int $id
     *  The id of the chat room the check for new messages.
     * @retval int
     *  The number of new messages in a chat room.
     */
    public function get_room_message_count($id)
    {
        $sql = "SELECT COUNT(c.id) AS count FROM chat AS c
            WHERE c.is_new = '1' AND c.id_rcv_grp = :gid AND c.id_snd != :me
                AND c.id_rcv = :me";
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
        return ($this->gid !== null);
    }

    /**
     * Insert the chat item to the database. In the role of a subject, no user
     * recipiant is specified only a recipiant chat room.
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
            "content" => $msg,
            "is_new" => '1',
        ));
    }
}
?>
