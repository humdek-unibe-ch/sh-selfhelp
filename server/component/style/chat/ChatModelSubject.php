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
     *  The group id
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
            WHERE c.id_rcv_group = :gid AND (receiver.id_users = :me
                OR c.id_snd = :me)
            ORDER BY c.timestamp";
        return $this->db->query_db($sql, array(
            ":me" => $_SESSION['id_user'],
            ":gid" => $this->gid
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
        return $this->is_user_in_group($_SESSION['id_user']);
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
        try {
            $this->db->begin_transaction();
            $msg_id = $this->db->insert("chat", array(
                "id_snd" => $_SESSION['id_user'],
                "id_rcv_group" => $this->gid,
                "content" => $msg,
            ));
            if ($msg_id) {
                // send message to all therapsit in the group
                $sql = "SELECT DISTINCT ug.id_users, :cid AS id_chat
                            FROM users_groups ug 
                            INNER JOIN users_groups ug2 on (ug.id_users = ug2.id_users)
                            INNER JOIN acl_groups acl ON (acl.id_groups = ug2.id_groups)
                            INNER JOIN pages p ON (acl.id_pages = p.id)
                            WHERE ug.id_groups = :gid AND p.keyword = 'chatTherapist'
                            GROUP BY  ug.id_users
                            HAVING MAX(IFNULL(acl.acl_select, 0)) = 1";
                $users = $this->db->query_db($sql, array(
                    ':cid' => $msg_id,
                    ':gid' => $this->gid,
                ));
                foreach ($users as $user) {
                    $this->db->insert('chatRecipiants', $user);
                    try {
                        $url = "https://" . $_SERVER['HTTP_HOST'] . $this->get_link_url('chatTherapist', array(
                            'gid' => $this->gid,
                            'uid' => intval($user['id_users'])
                        ));
                        $this->notify(intval($user['id_users']), $url);
                    } catch (Exception $e) {
                        // mail was not sent
                    }
                }
            } else {
                $this->db->rollback();
                return false;
            }
            $this->db->commit();
            return $msg_id;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }       
    }
}
?>
