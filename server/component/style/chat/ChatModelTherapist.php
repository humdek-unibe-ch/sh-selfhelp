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

    /**
     * The list of groups.
     */
    protected $groups;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all chat related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section id of the chat wrapper.
     * @param int $chrid
     *  The chat room id to communicate with
     * @param int $uid
     *  The user id to communicate with
     */
    public function __construct($services, $id, $chrid, $gid, $uid)
    {
        parent::__construct($services, $id, $chrid, $gid, $uid);
        $this->groups = $this->fetch_groups();
    }

    /* Private Methods ******************************************************/

    /**
     * Fetch the list of groups except the default 3 (admin, therapist and subject) whic has access to chat
     *
     * @retval array
     *  A list of db items where each item has the keys
     *   'id':      The id of the group.
     *   'name':    The name of the group.
     */
    private function fetch_groups()
    {
        $sql = "SELECT g.id, g.name 
                FROM groups AS g
                inner join acl_groups acl on (acl.id_groups = g.id)
                inner join pages p on (acl.id_pages = p.id) 
                WHERE g.id > 3 and acl.acl_select = 1 and p.keyword = 'contact'
                ORDER BY g.name";
        return $this->db->query_db($sql);
    }

    /**
     * Search for key value in a nested array
     *
     * @retval boolean
     */
    function find_key_value($array, $key, $val)
    {
        foreach ($array as $item) {
            if (is_array($item) && $this->find_key_value($item, $key, $val)) return true;

            if (isset($item[$key]) && $item[$key] == $val) return true;
        }

        return false;
    }

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
            WHERE c.id_rcv_grp = :rid AND (
                (receiver.id_users = :me
                    AND (c.id_snd = :uid OR c.id_rcv = :uid))
                OR (c.id_snd = :me AND receiver.id_users = :uid)
            )
            ORDER BY c.timestamp";
        return $this->db->query_db($sql, array(
            ":me" => $_SESSION['id_user'],
            ":rid" => $this->chrid == 0 ? GLOBAL_CHAT_ROOM_ID : $this->chrid, // showing mesage for group tabs
            ":uid" => $this->uid,
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
     * Get all subjects based on selected chat room or loby
     *
     * @retval array
     *  The database result with the following keys:
     *   'id':      The user id of the subject.
     *   'name':    The name of the subject.
     */

    public function get_Subjects()
    {
        if ($this->chrid == GLOBAL_CHAT_ROOM_ID) {
            return $this->get_LobySubjects();
        } else if (($this->chrid == 0)) {
            // group is selected
            if ($this->find_key_value($this->groups, 'id', $this->gid)) {
                //check if the group param in in groups
                return $this->get_GroupSubjects();
            } else {
                // otherwise return emty array
                return array();
            }
        } else {
            return $this->get_RoomSubjects();
        }
    }

    /**
     * Get all subjects in a the selected group except me
     *
     * @retval array
     *  The database result with the following keys:
     *   'id':      The user id of the subject.
     *   'name':    The name of the subject.
     */
    public function get_GroupSubjects()
    {
        $sql = "SELECT DISTINCT u.id, u.name 
                FROM users AS u
                LEFT JOIN users_groups AS ug ON ug.id_users = u.id
                WHERE ug.id_groups = :gid and u.id <> :me";
        return $this->db->query_db($sql, array(
            ":gid" => $this->gid,
            ":me" => $_SESSION['id_user']
        ));
    }

    /**
     * Get all subjects in a the selected room.
     *
     * @retval array
     *  The database result with the following keys:
     *   'id':      The user id of the subject.
     *   'name':    The name of the subject.
     */
    public function get_RoomSubjects()
    {
        $sql = "SELECT DISTINCT u.id, u.name 
                FROM users AS u
                LEFT JOIN chatRoom_users AS chru ON chru.id_users = u.id
                WHERE chru.id_chatRoom = :rid and u.id <> :me";
        return $this->db->query_db($sql, array(
            ":rid" => $this->chrid,
            ":me" => $_SESSION['id_user']
        ));
    }

    /**
     * Get all subjects in a the selected room which have written a msg.
     * This is for the Loby
     *
     * @retval array
     *  The database result with the following keys:
     *   'id':      The user id of the subject.
     *   'name':    The name of the subject.
     */
    public function get_LobySubjects()
    {
        // $sql = "SELECT DISTINCT u.id, u.name FROM users AS u
        //     LEFT JOIN chat AS c ON c.id_snd = u.id
        //     LEFT JOIN users_groups AS ug ON ug.id_users = u.id
        //     WHERE c.id_rcv_grp = :rid AND ug.id_groups = :gid";
        // return $this->db->query_db($sql, array(
        //     ":gid" => SUBJECT_GROUP_ID,
        //     ":rid" => $this->gid,
        // ));
        $sql = "SELECT DISTINCT u.id, u.name 
            FROM users AS u
            LEFT JOIN chat AS c ON c.id_snd = u.id
            LEFT JOIN users_groups AS ug ON ug.id_users = u.id
            WHERE c.id_rcv_grp = :rid and u.id <> :uid";
        return $this->db->query_db($sql, array(
            ":rid" => $this->chrid,
            ":uid" => $_SESSION['id_user']
        )); // visualize the messages for all users not for these who are only in group subject except myself
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
            "contact",
            array("gid" => $this->gid, "chrid" => $this->chrid, "uid" => $uid)
        );
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
        $sql = "SELECT COUNT(cr.id_chat) AS count FROM chatRecipiants AS cr
            LEFT JOIN chat AS c ON c.id = cr.id_chat
            WHERE cr.is_new = '1' AND cr.id_users = :me
                AND (c.id_snd = :uid OR c.id_rcv = :uid)
                AND c.id_rcv_grp = :rid";
        $res = $this->db->query_db_first($sql, array(
            ':uid' => $id,
            ':me' => $_SESSION['id_user'],
            ':rid' => $this->chrid == 0 ? GLOBAL_CHAT_ROOM_ID : $this->chrid, //if it is in group get the loby messages
        ));
        if ($res)
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
        return ($this->chrid !== null && $this->uid !== null);
        //return ($this->gid !== null);
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
        $msg_id = $this->db->insert("chat", array(
            "id_snd" => $_SESSION['id_user'],
            "id_rcv" => $this->uid,
            "id_rcv_grp" => $this->chrid == 0 ? GLOBAL_CHAT_ROOM_ID : $this->chrid,
            "content" => $msg,
        ));
        if ($msg_id) {
            if ($this->chrid === GLOBAL_CHAT_ROOM_ID || $this->chrid === 0) {
                // send to all therapists but me and the user
                // added disitnct in the query otherwise a person with multiple groups send a few insert requests
                $sql = "SELECT DISTINCT ug.id_users AS id_users, :cid AS id_chat
                    FROM users_groups AS ug
                    WHERE (ug.id_groups = :gid AND ug.id_users != :me)
                        OR ug.id_users = :uid";
                $users = $this->db->query_db($sql, array(
                    ':cid' => $msg_id,
                    ':gid' => EXPERIMENTER_GROUP_ID,
                    ':uid' => $this->uid,
                    ':me' => $_SESSION['id_user'],
                ));
            } else {
                // send to all therapists but me and the user in the room
                // added disitnct in the query otherwise a person with multiple groups send a few insert requests
                $sql = "SELECT DISTINCT cru.id AS id_room_users, cru.id_users AS id_users,
                    :cid AS id_chat FROM chatRoom_users AS cru
                    LEFT JOIN users_groups AS ug ON ug.id_users = cru.id_users
                    WHERE cru.id_chatRoom = :rid
                        AND ((ug.id_groups = :gid AND ug.id_users != :me)
                            OR ug.id_users = :uid)";
                $users = $this->db->query_db($sql, array(
                    ':rid' => $this->chrid,
                    ':cid' => $msg_id,
                    ':gid' => EXPERIMENTER_GROUP_ID,
                    ':uid' => $this->uid,
                    ':me' => $_SESSION['id_user'],
                ));
            }
            foreach ($users as $user) {
                $this->db->insert('chatRecipiants', $user);
                $this->notify(intval($user['id_users']));
            }
        }
        return $msg_id;
    }

    /**
     * Get the list of groups.
     *
     * @retval array
     *  The result from the db query see ChatModel::fetch_groups().
     */
    public function get_groups()
    {
        return $this->groups;
    }
}
?>
