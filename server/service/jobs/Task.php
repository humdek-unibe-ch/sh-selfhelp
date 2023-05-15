<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../globals_untracked.php";
require_once __DIR__ . "/BasicJob.php";

/**
 * A task class repsonsible for tasks
 */
class Task  extends BasicJob
{

    /**
     * Creating a PHPMailer Instance.
     *
     * @param object $db
     *  An instcance of the service class PageDb.
     */
    public function __construct($db, $transaction, $condition)
    {
        parent::__construct($db, $transaction, $condition);
    }

    /* Private Methods *********************************************************/

    /**
     * Execute task
     * @param object $task_info
     * The info for the executing task
     * @param string $sent_by
     * the type which the task queue execution was triggered
     * @param int $id_users  
     * the user to whom the task is executed
     * @param int $execute_user_id  
     * the user who executed the task, null if it was automated
     * @return boolean
     * Return the result of the task
     */
    private function execute_task($task_info, $sent_by, $user, $execute_user_id)
    {
        $res = true;
        if ($task_info['config']['type'] == "add_group") {
            // add group to user
            $res = $this->add_group_to_user($task_info, $sent_by, $user['id_users'], $execute_user_id) && $res;
        } else if ($task_info['config']['type'] == "remove_group") {
            // remove group from user
            $res = $this->remove_group_from_user($task_info, $sent_by, $user['id_users'], $execute_user_id)  && $res;
        }
        return $res;
    }

    /**
     * Execute task single     
     * @param array $task_info
     * Info for the task queue entry
     * @param string  $sent_by  
     * the type which the task queue execution was triggered
     * @param int $execute_user_id  
     * the user who executed the task, null if it was automated
     * @return boolean
     *  return if task was successfully executed
     */
    private function execute_task_single($task_info, $sent_by, $condition, $execute_user_id)
    {
        $res = true;
        $sql = "SELECT *
                FROM scheduledJobs_users sj_u
                WHERE sj_u.id_scheduledJobs = :sj_id";
        $users = $this->db->query_db($sql, array(":sj_id" => $task_info['id']));
        $condition = $task_info['config'];
        $task_info['config'] = json_decode($task_info['config'], true);
        $condition = isset($task_info['config']['condition']) ? json_encode($task_info['config']['condition']) : $condition;
        foreach ($users as $user) {
            if ($condition == '' || $this->check_condition($condition, $user['id_users'])) {
                // check if no condition or condition fulfilled -> then execute
                $res = $this->execute_task($task_info, $sent_by, $user, $execute_user_id);
            } else {
                $this->transaction->add_transaction(
                    transactionTypes_send_notification_fail,
                    $sent_by,
                    $execute_user_id,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $task_info['id'],
                    false,
                    'Executing task for user: ' . $user['id_users'] . ' failed because the condition was not meat'
                );
                $res = false;
            }
        }
        return $res;
    }

    /**
     * Add group to user     
     * @param array $task_info
     * Info for the task queue entry
     * @param string  $sent_by  
     * the type which the task queue execution was triggered
     * @param int $id_users  
     * the user to whom we will add a group
     * @param int $execute_user_id  
     * the user who executed the task, null if it was automated
     * @retval boolean
     *  return true if group(s) was/were scuccessfully assigned
     */
    private function add_group_to_user($task_info, $sent_by, $id_users, $execute_user_id)
    {
        $res = true;
        foreach ($task_info['config']['group'] as $key => $group) {
            $id_groups = $this->getGroupId($group);
            if ($id_groups) {
                // group exist
                $add_res = $this->db->insert(
                    'users_groups',
                    array(
                        'id_groups' => $id_groups,
                        'id_users' => $id_users
                    )
                );
                $this->transaction->add_transaction(
                    $add_res ? transactionTypes_execute_task_ok : transactionTypes_execute_task_fail,
                    $sent_by,
                    $execute_user_id,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $task_info['id'],
                    false,
                    'Add group ' . $group . ' to user: ' . $id_users
                );
                $res = $res && $add_res;
            } else {
                $res = false;
                $this->transaction->add_transaction(
                    transactionTypes_execute_task_fail,
                    $sent_by,
                    $execute_user_id,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $task_info['id'],
                    false,
                    'There is no group: ' . $group . '. It was not added to user: ' . $id_users
                );
            }
        }
        return $res;
    }

    /**
     * Remove group from user  
     * @param array $task_info
     * Info for the task queue entry
     * @param string  $sent_by  
     * the type which the task queue execution was triggered
     * @param int $id_users  
     * the user to whom we will add a group
     * @param int $execute_user_id  
     * the user who executed the task, null if it was automated
     * @retval boolean
     *  return true if group(s) was/were scuccessfully assigned
     */
    private function remove_group_from_user($task_info, $sent_by, $id_users, $execute_user_id)
    {
        $res = true;
        foreach ($task_info['config']['group'] as $key => $group) {
            $id_groups = $this->getGroupId($group);
            if ($id_groups) {
                // group exist
                $add_res = $this->db->remove_by_ids(
                    'users_groups',
                    array(
                        'id_groups' => $id_groups,
                        'id_users' => $id_users
                    )
                );
                $this->transaction->add_transaction(
                    $add_res ? transactionTypes_execute_task_ok : transactionTypes_execute_task_fail,
                    $sent_by,
                    $execute_user_id,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $task_info['id'],
                    false,
                    'Remove group ' . $group . ' from user: ' . $id_users
                );
                $res = $res && $add_res;
            } else {
                $res = false;
                $this->transaction->add_transaction(
                    transactionTypes_execute_task_fail,
                    $sent_by,
                    $execute_user_id,
                    $this->transaction::TABLE_SCHEDULED_JOBS,
                    $task_info['id'],
                    false,
                    'There is no group: ' . $group . '. It was not removed from user: ' . $id_users
                );
            }
        }
        return $res;
    }

    /**
     * Get the group id
     *
     * @param $group
     *  The name of a group
     * @return $groupId
     *  the id of the group or false on failure
     */
    private function getGroupId($group)
    {
        $sql = "SELECT id FROM `groups`
            WHERE name = :group";
        $res = $this->db->query_db_first($sql, array(':group' => $group));
        return  !isset($res['id']) ? false : $res['id'];
    }

    /* Public Methods *********************************************************/

    /**
     * Insert a notification record and set the users in table notifications_users
     * @param array $sj_id
     * schedule job id
     * @param array $data
     * array with the data
     * @retval boolean
     *  return if the insert is successful
     */
    public function schedule($sj_id, $data)
    {
        try {
            $this->db->begin_transaction();
            $task = array(
                "config" => json_encode($data['config'], true)
            );
            $task_id = $this->db->insert('tasks', $task);
            if ($task_id) {
                foreach ($data['id_users'] as $user) {
                    $this->db->insert('scheduledJobs_users', array(
                        "id_users" => $user,
                        "id_scheduledJobs" => $sj_id
                    ));
                }
            }
            $this->db->commit();
            return $task_id;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function execute_entry($sj_id, $sent_by, $condition, $user_id = null)
    {
        $task_info = $this->db->select_by_uid('view_tasks', $sj_id);
        if ($task_info) {
            return $this->execute_task_single($task_info, $sent_by, $condition, $user_id);
        } else {
            return false;
        }
    }
}
?>
