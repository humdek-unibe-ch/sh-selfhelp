<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     * Get the chat items. Get all items, associated to a group where the
     * current user is the recipiant and all items the current user has sent.
     *
     * @retval array
     *  See ChatModel::get_chat_items_spec()
     */
    protected function get_chat_items_spec()
    {
        $sql = "SELECT c.id AS cid, sender.id AS uid, sender.name AS name,
            c.content AS msg, c.timestamp, receiver.is_new
            FROM chat AS c
            LEFT JOIN users AS sender ON sender.id = c.id_snd
            LEFT JOIN chatRecipiants AS receiver ON c.id = receiver.id_chat AND receiver.id_users = :me
            WHERE c.id_rcv_grp = :rid AND (receiver.id_users = :me
                OR c.id_snd = :me)
            ORDER BY c.timestamp";
        return $this->db->query_db($sql, array(
            ":me" => $_SESSION['id_user'],
            ":rid" => $this->gid,
        ));
    }

    /* Public Methods *********************************************************/

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
        $msg_id = $this->db->insert("chat", array(
            "id_snd" => $_SESSION['id_user'],
            "id_rcv_grp" => $this->gid,
            "content" => $msg,
        ));
        if($msg_id)
        {
            if($this->gid === GLOBAL_CHAT_ROOM_ID)
            {
                // send to all therapists
                $sql = "SELECT ug.id_users AS id_users, :cid AS id_chat
                    FROM users_groups AS ug
                    WHERE ug.id_groups = :gid";
                $users = $this->db->query_db($sql, array(
                    ':cid' => $msg_id,
                    ':gid' => EXPERIMENTER_GROUP_ID,
                ));
            }
            else
            {
                // send to all therapists in the room
                $sql = "SELECT cru.id AS id_room_users, cru.id_users AS id_users,
                    :cid AS id_chat FROM chatRoom_users AS cru
                    LEFT JOIN users_groups AS ug ON ug.id_users = cru.id_users
                    WHERE cru.id_chatRoom = :rid AND ug.id_groups = :gid";
                $users = $this->db->query_db($sql, array(
                    ':rid' => $this->gid,
                    ':cid' => $msg_id,
                    ':gid' => EXPERIMENTER_GROUP_ID,
                ));
            }
            foreach($users as $user)
            {
                $this->db->insert('chatRecipiants', $user);
                $this->notify(intval($user['id_users']));
            }
        }
        return $msg_id;
    }
}
?>
