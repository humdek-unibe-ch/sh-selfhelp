<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     *  The group id identifying the chat room
     * @param int $uid
     *  The user id to communicate with
     */
    public function __construct($services, $id, $gid, $uid)
    {
        parent::__construct($services, $id, $gid, $uid);        
    }

    /* Private Methods ******************************************************/    

    /* Protected Methods ******************************************************/

    /**
     * Get the chat items. Get all items, associated to a group where the
     * current user is the recipiant and the active user is the sender or
     * recipiant and all items the current user has sent to the active user.
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
            LEFT JOIN chatRecipiants AS receiver ON c.id = receiver.id_chat
            WHERE (c.id_rcv_group = :gid AND (
                (receiver.id_users = :me
                    AND (c.id_snd = :uid OR c.id_rcv = :uid))
                OR (c.id_snd = :me AND receiver.id_users = :uid)
            ))
            ORDER BY c.timestamp";
        return $this->db->query_db($sql, array(
            ":me" => $_SESSION['id_user'],
            ":uid" => $this->uid,
            ":gid" => $this->gid
        ));
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
        if ($name)
            return $name["name"];
        else
            return "";
    }

    /**
     * Get all subjects in a the selected group except these who are therapists
     *
     * @retval array
     *  The database result with the following keys:
     *   'id':      The user id of the subject.
     *   'name':    The name of the subject.
     */
    public function get_GroupSubjects()
    {
        $sql = "SELECT DISTINCT u.id, u.name, vc.code, ug.id_groups,
                (SELECT COUNT(cr.id_chat) AS count FROM chatRecipiants AS cr
                    LEFT JOIN chat AS c ON c.id = cr.id_chat
                    WHERE cr.is_new = '1' AND cr.id_users = :me
                        AND (c.id_snd = u.id OR c.id_rcv = u.id) AND c.id_rcv_group = :gid) AS count
                FROM users AS u
                LEFT JOIN users_groups AS ug ON ug.id_users = u.id
                LEFT JOIN validation_codes vc on vc.id_users = u.id
                INNER JOIN users_groups ug2 on (ug.id_users = ug2.id_users)
                INNER JOIN acl_groups acl ON (acl.id_groups = ug2.id_groups)
                INNER JOIN pages p ON (acl.id_pages = p.id)
                WHERE ug.id_groups = :gid AND p.keyword = 'chatTherapist' 
                GROUP BY  u.id, u.name, vc.code, ug.id_groups
                HAVING MAX(IFNULL(acl.acl_select, 0)) = 0
                ORDER BY count DESC, u.name";
        return $this->db->query_db($sql, array(
            ":gid" => $this->gid,
            ":me" => $_SESSION["id_user"]
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
        return $this->get_link_url(
            "chatTherapist",
            array("gid" => $this->gid, "uid" => $uid)
        );
    }

    /**
     * Checks whether all required parameters are set.
     *
     * @retval bool
     *  True if chat is ready, false otherwise.
     */
    public function is_chat_ready()
    {
        return $this->is_user_in_group($_SESSION['id_user']) && $this->is_user_in_group($this->uid);
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
     * If a message is send via the group tab is set to global chat.
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
                "id_rcv" => $this->uid,
                "id_rcv_group" => $this->gid,
                "content" => $msg,
            ));
            if ($msg_id) {
                // send message to all therapsit in the group plus the user. We exclude the sender as the message was already sent to him/her
                $sql = "SELECT DISTINCT ug.id_users, :cid AS id_chat
                            FROM users_groups ug 
                            INNER JOIN users_groups ug2 on (ug.id_users = ug2.id_users)
                            INNER JOIN acl_groups acl ON (acl.id_groups = ug2.id_groups)
                            INNER JOIN pages p ON (acl.id_pages = p.id)
                            WHERE ug.id_groups = :gid AND p.keyword = 'chatTherapist' AND ug.id_users != :me
                            GROUP BY  ug.id_users
                            HAVING MAX(IFNULL(acl.acl_select, 0)) = 1

                            UNION 

                            SELECT id, :cid AS id_chat 
                            FROM users
                            WHERE id = :uid";
                $users = $this->db->query_db($sql, array(
                    ':cid' => $msg_id,
                    ':gid' => $this->gid,
                    ':uid' => $this->uid,
                    ':me' => $_SESSION['id_user'],
                ));
                foreach ($users as $user) {
                    $this->db->insert('chatRecipiants', $user);
                    try{
                        if (intval($user['id_users']) == $this->uid) {
                            // prepare the url for the subject
                            $url = "https://" . $_SERVER['HTTP_HOST'] . $this->get_link_url('chatSubject', array(
                                'gid' => $this->gid
                            ));
                        } else {
                            // prepare the url for the therapist
                            $url = "https://" . $_SERVER['HTTP_HOST'] . $this->get_link_url('chatTherapist', array(
                                'gid' => $this->gid,
                                'uid' => intval($user['id_users'])
                            ));
                        }
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
