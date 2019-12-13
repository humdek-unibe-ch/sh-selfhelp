<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseCallback.php";
require_once __DIR__ . "/../service/globals_untracked.php";

/**
 * A small class that handles callbak and set the group number for validation code
 * calls.
 */
class CallbackSetGroup extends BaseCallback
{
    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        parent::__construct($services);
    }

    /**
     * Assign group to code in the table validation codes
     *
     * @param $group
     * @param $code
     */
    private function assignGroupToCode($group, $code)
    {
        return (bool) $this->db->insert(
            'codes_groups',
            array('id_groups' => $group, 'code' => $code)
        );
    }

    /**
     * Get the group id
     *
     * @param $groupName
     * @return $groupId
     */
    private function getGroupId($group)
    {
        $sql = "SELECT id FROM groups
            WHERE name = :group";
        $res = $this->db->query_db_first($sql, array(':group' => $group));
        if (!isset($res['id'])) {
            return -1;
        } else {
            return $res['id'];
        }
    }

    /**
     * The setgroup function that can be called by the callback
     *
     * @param $data
     *  The POST data of the callback call:
     * callbackKey is expected from where the callback is initialized
     */
    public function set_group($data)
    {
        $callback_log_id = $this->insert_callback_log($_SERVER, $data);
        $result = [];
        $callback_status = CALLBACK_ERROR;
        $result['selfhelpCallback'] = 'selfelhp';
        if (!isset($data['callbackKey']) || CALLBACK_KEY !== $data['callbackKey']) {
            $result['selfhelpCallback'] = 'wrong callback key';
            echo json_encode($result);
            return $this->update_callback_log($callback_log_id, $result, $callback_status);;
        }
        if (!isset($data['group']) || !isset($data['code'])) {
            $result['selfhelpCallback'] = 'missing parameter: code or group';
            echo json_encode($result);
            return $this->update_callback_log($callback_log_id, $result, $callback_status);;
        }
        $groupId = $this->getGroupId($data['group']);
        $result['groupId'] = $groupId;
        if ($this->assignGroupToCode($groupId, $data['code'])) {
            $callback_status = CALLBACK_SUCCESS;
            $result['selfhelpCallback'] = 'Code: ' . $data['code'] . ' was assigned to group: ' . $groupId . ' with name: ' . $data['group'];
        } else {
            $result['selfhelpCallback'] = 'Failed! Code: ' . $data['code'] . ' was not assigned to group: ' . $groupId . ' with name: ' . $data['group'];
        }
        $this->update_callback_log($callback_log_id, $result, $callback_status);
        echo json_encode($result);
    }
}
?>
