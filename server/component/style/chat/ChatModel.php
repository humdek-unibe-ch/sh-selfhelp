<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the chat component such
 * that the data can easily be displayed in the view of the component.
 *
 * This is a base class and needs to be extended by a role (either subject or
 * therapist).
 */
abstract class ChatModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The active group id to communicate with.
     */
    protected $gid = null;

    /**
     * The active user id to communicate with.
     */
    protected $uid = null;

    /**
     * The list of rooms.
     */
    protected $rooms;

    /**
     * DB field 'email_user' (empty string)
     * The notification email to be sent to the receiver of the chat msg.
     */
    private $email_user;

    /**
     * DB field 'subject_user' (empty string)
     * The subject of the notification email to be sent to the receiver of the
     * chat msg.
     */
    private $subject_user;

    /**
     * DB field 'is_html' (false)
     * If true, send the notification email as HTML, otherwise as plaintext.
     */
    private $is_html;

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
    public function __construct($services, $id, $gid, $uid=null)
    {
        parent::__construct($services, $id);
        $this->gid = $gid ?? GLOBAL_CHAT_ROOM_ID;
        $this->uid = $uid;
        $this->rooms = $this->fetch_rooms();
        $this->email_user = $this->get_db_field("email_user");
        $this->subject_user = $this->get_db_field("subject_user");
        $this->is_html = $this->get_db_field("is_html", false);
    }

    /* Private Methodes *******************************************************/

    /**
     * Get all rooms the current user is assigned to.
     *
     * @retval array
     *  The database result with the following keys:
     *   'id':      The room id.
     *   'name':    The name of the room.
     */
    private function fetch_rooms()
    {
        $sql = "SELECT r.id, r.name FROM chatRoom AS r
            LEFT JOIN chatRoom_users AS cu ON cu.id_chatRoom = r.id
            WHERE cu.id_users = :uid";
        return $this->db->query_db($sql, array(":uid" => $_SESSION['id_user']));
    }

    /* Abstract Protected Methodes ********************************************/

    /**
     * Get the chat items. This is role specific.
     *
     * @retval array
     *  The database result with the following keys:
     *   - 'uid':         The id of the user who sent the chat item.
     *   - 'name':        The name of the user who sent the chat item.
     *   - 'msg':         The content of the chat item.
     *   - 'timestamp':   The timestamp of when the chat item was sent.
     *   - 'is_new':      Indicates whether the message is new.
     */
    abstract protected function get_chat_items_spec();

    /* Abstract Public Methodes ***********************************************/

    /**
     * Checks whether all required parameters are set. This depends on the role.
     *
     * @retval bool
     *  True if chat is ready, false otherwise.
     */
    abstract public function is_chat_ready();

    /**
     * Notyfy a user about a new chat message.
     *
     * @param int $id
     *  The id of the user to be notified.
     */
    protected function notify($id)
    {
        $subject = $this->subject_user;
        $from = array('address' => "noreply@" . $_SERVER['HTTP_HOST']);
        $url = "https://" . $_SERVER['HTTP_HOST']
            . $this->get_link_url('contact');
        $msg = str_replace('@link', $url, $this->email_user);
        $msg_html = $this->is_html ? $this->parsedown->text($msg) : null;
        $field_chat = $this->user_input->get_input_fields(array(
            'page' => 'profile',
            'id_user' => $id,
            'form_name' => 'notification',
            'field_name' => 'chat',
        ));
        if(count($field_chat) === 0 || $field_chat[0]['value'] !== "")
        {
            $sql = "SELECT email FROM users WHERE id = :id";
            $email = $this->db->query_db_first($sql, array(':id' => $id));
            $to = $this->mail->create_single_to($email['email']);
            $this->mail->send_mail($from, $to, $subject, $msg);
        }
        $field_phone = $this->user_input->get_input_fields(array(
            'page' => 'profile',
            'id_user' => $id,
            'form_name' => 'notification',
            'field_name' => 'phone',
        ));
        if(count($field_phone) === 1 && $field_phone[0]['value'] !== "")
        {
            $email = $field_phone[0]['value'] . "@sms.unibe.ch";
            $to = $this->mail->create_single_to($email);
            $this->mail->send_mail($from, $to, $subject, $msg, $msg_html);
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Get the chat itmes. If the current user is an therapist all chat items
     * related to a selected user are returned. If the current user is not an
     * experimenter all chat items related to the current user are returned.
     *
     * @retval array
     *  See ChatModel::get_chat_items_spec()
     */
    public function get_chat_items()
    {
        $items = $this->get_chat_items_spec();
        $ids = array();
        foreach($items as $item)
        {
            if($item['uid'] == $_SESSION['id_user'])
                continue;
            $ids[] = $item['cid'];
        }
        if(count($ids) > 0)
        {
            $sql = 'UPDATE chatRecipiants SET is_new = 0 WHERE id_chat in (' . implode(',', $ids) . ') AND id_users = ' . $_SESSION['id_user'];
            $this->db->execute_db($sql);
        }
        return $items;
    }

    /**
     * Get the number of new room messages.
     *
     * @param int $id
     *  The id of the chat room the check for new messages.
     * @retval int
     *  The number of new messages in a chat room.
     */
    public function get_room_message_count($id)
    {
        $sql = "SELECT COUNT(cr.id_chat) AS count FROM chatRecipiants AS cr
            LEFT JOIN chat AS c ON c.id = cr.id_chat
            WHERE cr.is_new = '1' AND cr.id_users = :me
                AND (c.id_rcv_grp = :gid
                    OR (:gid = :ggid AND cr.id_room_users IS NULL))";
        $res = $this->db->query_db_first($sql, array(
            ':gid' => $id,
            ':me' => $_SESSION['id_user'],
            ':ggid' => GLOBAL_CHAT_ROOM_ID,
        ));
        if($res)
            return intval($res['count']);
        return 0;
    }


    /**
     * Get the list of rooms.
     *
     * @retval array
     *  The result from the db query see ChatModel::fetch_rooms().
     */
    public function get_rooms()
    {
        return $this->rooms;
    }

    /**
     * Checks whether the current user is in the active group.
     *
     * @retval bool
     *  True if the user is in the active group, false otherwise.
     */
    public function is_current_user_in_active_group()
    {
        $sql = "SELECT * FROM chatRoom_users
            WHERE id_chatRoom = :rid AND id_users = :uid";
        $res = $this->db->query_db($sql, array(
            ":rid" => $this->gid,
            ":uid" => $_SESSION['id_user'],
        ));
        return ($res || $this->gid === GLOBAL_CHAT_ROOM_ID);
    }

    /**
     * Checks whether a given room is currently selected.
     *
     * @param int $id
     *  The id of the chat room to check.
     * @retval bool
     *  True if the given room is selected, false otherwise.
     */
    public function is_room_selected($id)
    {
        return ($id === $this->gid);
    }

    /**
     * Insert the chat item to the database. This method depends on the role.
     *
     * If the current user is an
     * experimenter the chat item is sent to the selected user. If the current
     * user is not an experimenter, the chat item is sent to the experimenter
     * group (i.e. no recipiant is specified).
     *
     * @param string $msg
     *  The chat item content.
     * @retval int
     *  The id of the chat item on success, false otherwise.
     */
    abstract public function send_chat_msg($msg);
}
?>
