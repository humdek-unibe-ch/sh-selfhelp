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
     * @param int $gid
     *  The group id to communicate with
     * @param int $uid
     *  The user id to communicate with
     */
    public function __construct($services, $id, $gid=null, $uid=null)
    {
        parent::__construct($services, $id);
        $this->gid = $gid;
        $this->uid = $uid;
        $this->email_user = $this->get_db_field("email_user");
        $this->subject_user = $this->get_db_field("subject_user");
        $this->is_html = $this->get_db_field("is_html", false);
        $this->groups = $this->fetch_groups();
    }

    /* Private Methodes *******************************************************/


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
    protected function notify($id, $url)
    {
        $subject = $this->subject_user;
        $from = "noreply@" . $_SERVER['HTTP_HOST'];
        $url = $url;
        $msg = str_replace('@link', $url, $this->email_user);
        $msg_html = $this->is_html ? $this->parsedown->text($msg) : $msg;
        $field_chat = $this->user_input->get_input_fields(array(
            'page' => 'profile',
            'id_user' => $id,
            'form_name' => 'notification',
            'field_name' => 'chat',
        ));
        if (count($field_chat) === 0 || $field_chat[0]['value'] !== "") {
            $sql = "SELECT email FROM `users` WHERE id = :id";
            $email = $this->db->query_db_first($sql, array(':id' => $id));
            $mail = array(
                "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
                "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
                "date_to_be_executed" => date('Y-m-d H:i:s', time()),
                "from_email" => $from,
                "from_name" => $from,
                "reply_to" => $from,
                "recipient_emails" => $email['email'],
                "subject" => $subject,
                "body" => $msg_html,
                "description" => "Chat notification email"
            );
            $this->job_scheduler->add_and_execute_job($mail, transactionBy_by_user);
        }
        $field_phone = $this->user_input->get_input_fields(array(
            'page' => 'profile',
            'id_user' => $id,
            'form_name' => 'notification',
            'field_name' => 'phone',
        ));
        if (count($field_phone) === 1 && $field_phone[0]['value'] !== "") {
            $email = $field_phone[0]['value'] . "@sms.unibe.ch";
            $mail = array(
                "id_jobTypes" => $this->db->get_lookup_id_by_value(jobTypes, jobTypes_email),
                "id_jobStatus" => $this->db->get_lookup_id_by_value(scheduledJobsStatus, scheduledJobsStatus_queued),
                "date_to_be_executed" => date('Y-m-d H:i:s', time()),
                "from_email" => $from,
                "from_name" => $from,
                "reply_to" => $from,
                "recipient_emails" => $email,
                "subject" => $subject,
                "body" => $msg_html,
                "description" => "Chat notification SMS"
            );
            $this->job_scheduler->add_and_execute_job($mail, transactionBy_by_user);
        }
    }

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
        $sql = "SELECT g.id, g.`name` 
                FROM `groups` AS g
                INNER JOIN acl_groups acl ON (acl.id_groups = g.id)
                INNER JOIN pages p ON (acl.id_pages = p.id) 
                INNER JOIN users_groups ug ON (ug.id_groups = g.id) 
                INNER JOIN `users` u ON (u.id = ug.id_users)
                WHERE g.id > 2 AND acl.acl_select = 1 AND p.keyword = 'chatSubject' AND u.id = :uid
                ORDER BY g.id";
        return $this->db->query_db($sql, array(":uid"=>$_SESSION['id_user']));
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
     * Get the number of new group messages.
     *
     * @param int $id
     *  The id of the group room the check for new messages.
     * @retval int
     *  The number of new messages in a group room.
     */
    public function get_group_message_count($id)
    {
        $sql = "SELECT COUNT(cr.id_chat) AS count 
                FROM chatRecipiants AS cr
                LEFT JOIN chat AS c ON c.id = cr.id_chat
                WHERE cr.is_new = '1' AND cr.id_users = :me and c.id_rcv_group = :gid";
        $res = $this->db->query_db_first($sql, array(
            ':me' => $_SESSION['id_user'],
            ':gid' => $id
        ));
        if($res)
            return intval($res['count']);
        return 0;
    }

    /**
     * Checks whether a given group is currently selected.
     *
     * @param int $id
     *  The id of the group to check.
     * @retval bool
     *  True if the given group is selected, false otherwise.
     */
    public function is_group_selected($id)
    {
        return ($id === $this->gid);
    }

    /**
     * Check if user is in the group
     * @param $user_id
     * user id that we want to check wheather it is in the group
     * @retval bool
     * True if the user is in the group
     */
    public function is_user_in_group($user_id)
    {
        $sql = "SELECT u.id, u.`name`
                FROM users AS u
                INNER JOIN users_groups AS ug ON ug.id_users = u.id
                WHERE ug.id_groups = :gid and u.id = :uid";
        $res = $this->db->query_db($sql, array(
            ":gid" => $this->gid,
            ":uid" => $user_id,
        ));
        return count($res) > 0;
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
