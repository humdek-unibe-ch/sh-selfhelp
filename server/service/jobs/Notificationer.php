<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../globals_untracked.php";
require_once __DIR__ . "/../ext/firebase-php/vendor/autoload.php";
require_once __DIR__ . "/BasicJob.php";

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

/**
 * A wrapper class for PHPMailer. It provides a simple email sending method
 * which should be usable throughout this rpoject.
 */
class Notificaitoner extends BasicJob
{
    /**
     * User input service.
     */
    private $user_input = null;

    /**
     * Firebase Messaging instance.
     */
    private $messaging;

    /**
     * Creating a PHPNotification Instance.
     *
     * @param object $db
     *  An instance of the service class PageDb.
     */
    public function __construct($db, $transaction, $condition, $user_input)
    {
        $this->user_input = $user_input;
        parent::__construct($db, $transaction, $condition);
        $factory = (new Factory)->withServiceAccount(__DIR__ . '/serviceAccountKey.json');        
        $this->messaging = $factory->createMessaging();
    }

    /* Private Methods *********************************************************/

    /**
     * Send notification via fcm
     * @param string $device_token
     * the identifier
     * @param array $data
     * the notification data  
     * @return boolean
     * return true or false;
     */
    private function send_notification($device_token, $data)
    {
        // Create the notification
        $notification = Notification::create($data['subject'], $data['body']);

        // Create the message
        $message = CloudMessage::withTarget('token', $device_token)
            ->withNotification($notification)
            ->withDefaultSounds();

        if (isset($data['url'])) {
            $message = $message->withData(['url' => $data['url']]);
        }

        // Send the notification
        try {
            $this->messaging->send($message);
            return true;
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            // Handle the exception
            return false;
        }
    }

    /**
     * Send mail from the queue
     * @param int $mail_queue_id 
     * the mail queue id from where we will take the information for the fields that we will send
     * @param array $notification_info
     * Info for the mail queue entry
     * @param string  $sent_by  
     * the type which the email queue sent was triggered
     * @param int $user_id  
     * the user who sent the email, null if it was automated
     * @return boolean
     *  return if mail was sent successfully
     */
    private function send_notification_single($notification_info, $sent_by, $condition, $user_id)
    {
        $res = true;
        $sql = "SELECT u.email, u.device_token, u.id AS id_users
                FROM scheduledJobs_users sj_u
                INNER JOIN users u ON (sj_u.id_users = u.id)
                WHERE sj_u.id_scheduledJobs = :sj_id";
        $notifications = $this->db->query_db($sql, array(":sj_id" => $notification_info['id']));
        foreach ($notifications as $notification) {
            $notification_settings = $this->user_input->get_user_notification_settings($notification['id_users']);
            if ($notification_settings && isset($notification_settings['no_push_notifications']) && $notification_settings['no_push_notifications']) {
                $this->transaction->add_transaction(
                    transactionTypes_send_notification_fail,
                    $sent_by,
                    $user_id,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $notification_info['id'],
                    false,
                    'Sending push notifications to user ' . $notification['email'] . ' failed because the user does not want to receive push notifications.'
                );
                return false;
            }
            if ($this->check_condition($condition, $notification['id_users'])) {
                if ($notification['device_token']) {
                    $user_info = $this->db->select_by_uid('view_user_codes', $notification['id_users']);
                    $notification_info['body'] = str_replace('@user_name', $user_info['name'], $notification_info['body']);
                    $notification_info['subject'] = str_replace('@user_name', $user_info['name'], $notification_info['subject']);
                    $res = $res && $this->send_notification($notification['device_token'], $notification_info);
                    $this->transaction->add_transaction(
                        $res ? transactionTypes_send_notification_ok : transactionTypes_send_notification_fail,
                        $sent_by,
                        $user_id,
                        $this->transaction::TABLE_SCHEDULED_JOBS,
                        $notification_info['id'],
                        false,
                        'Sending notification to ' . $notification['email']
                    );
                } else {
                    $this->transaction->add_transaction(
                        transactionTypes_send_notification_fail,
                        $sent_by,
                        $user_id,
                        $this->transaction::TABLE_SCHEDULED_JOBS,
                        $notification_info['id'],
                        false,
                        'Sending notification to ' . $notification['email'] . ' failed because the user has no device_token'
                    );
                    $res = false;
                }
            } else {
                $this->transaction->add_transaction(
                    transactionTypes_send_notification_fail,
                    $sent_by,
                    $user_id,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $notification_info['id'],
                    false,
                    'Sending notification to ' . $notification['email'] . ' failed because the condition was not meat'
                );
                $res = false;
            }
        }
        return $res;
    }

    /* Public Methods *********************************************************/

    /**
     * Insert a notification record and set the users in table notifications_users
     * @param array $sj_id
     * schedule job id
     * @param array $data
     * array with the data
     * @return boolean
     *  return if the insert is successful
     */
    public function schedule($sj_id, $data)
    {
        try {
            $this->db->begin_transaction();
            $notification = array(
                "body" => $data['body'],
                "subject" => $data['subject'],
                "url" => $data['url'],
            );
            $notification_id = $this->db->insert('notifications', $notification);
            if ($notification_id) {
                foreach ($data['recipients'] as $user) {
                    $this->db->insert('scheduledJobs_users', array(
                        "id_users" => $user,
                        "id_scheduledJobs" => $sj_id
                    ));
                }
            }
            $this->db->commit();
            return $notification_id;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function send_entry($sj_id, $sent_by, $condition, $user_id = null)
    {
        $mail_info = $this->db->select_by_uid('view_notifications', $sj_id);
        if ($mail_info) {
            return $this->send_notification_single($mail_info, $sent_by, $condition, $user_id);
        } else {
            return false;
        }
    }
}
?>
