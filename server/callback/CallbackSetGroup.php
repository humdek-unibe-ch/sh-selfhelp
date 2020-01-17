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
            array(
                'id_groups' => $group,
                'code' => $code
            )
        );
    }

    /**
     * Assign group to user in the table validation codes
     *
     * @param $group
     * @param $code
     */
    private function assignUserToGroup($group, $userId)
    {
        return (bool) $this->db->insert(
            'users_groups',
            array('id_groups' => $group, 'id_users' => $userId)
        );
        return false;
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
        return  !isset($res['id']) ? -1 : $res['id'];
    }

    /**
     * Check is the code is in database and available
     *
     * @param $code
     * @return $boolean
     */
    private function doesCodeExists($code)
    {
        $sql = "select code
                from validation_codes
                where code  = :code and consumed is null";
        $res = $this->db->query_db_first($sql, array(':code' => $code));
        return isset($res['code']);
    }

    /**
     * Check does a user with that code is registered
     *
     * @param $code
     * @return $boolean
     */
    private function getUserId($code)
    {
        $sql = "select id_users
                from validation_codes
                where code  = :code";
        $res = $this->db->query_db_first($sql, array(':code' => $code));
        return  !isset($res['id_users']) ? -1 : $res['id_users'];
    }

    /**
     * The set_group function that can be called by the callback
     * set group for a validation code; once this code is activated it will automatically assign the group to the created user
     *
     * @param $data
     *  The POST data of the callback call:
     * callbackKey is expected from where the callback is initialized
     */
    public function set_group($data)
    {
        $callback_log_id = $this->insert_callback_log($_SERVER, $data);
        $result = $this->validate_callback($data, false);
        if ($result['callback_status'] == CALLBACK_SUCCESS) {
            //validation passed; try to execute
            if ($this->assignGroupToCode($result['groupId'], $data['code'])) {
                $result['selfhelpCallback'] = 'Code: ' . $data['code'] . ' was assigned to group: ' . $result['groupId'] . ' with name: ' . $data['group'];
            } else {
                $result['selfhelpCallback'] = 'Failed! Code: ' . $data['code'] . ' was not assigned to group: ' . $result['groupId'] . ' with name: ' . $data['group'];
                $result['callback_status'] = CALLBACK_ERROR;
            }
        }
        $this->update_callback_log($callback_log_id, $result);
        echo json_encode($result);
    }

    /**
     * The set_group_for_user function that can be called by the callback
     * search user by validation code and if found assign group to that user
     *
     * @param $data
     *  The POST data of the callback call:
     * callbackKey is expected from where the callback is initialized
     */
    public function set_group_for_user($data)
    {
        $callback_log_id = $this->insert_callback_log($_SERVER, $data);
        $result = $this->validate_callback($data, true);
        if ($result['callback_status'] == CALLBACK_SUCCESS) {
            //validation passed; try to execute
            if ($this->assignUserToGroup($result['groupId'], $result['userId'])) {
                array_push($result['selfhelpCallback'], 'User with code: ' . $data['code'] . ' was assigned to group: ' . $result['groupId'] . ' with name: ' . $data['group']);
            } else {
                array_push($result['selfhelpCallback'], 'Failed! User with code: ' . $data['code'] . ' was not assigned to group: ' . $result['groupId'] . ' with name: ' . $data['group']);
                $result['callback_status'] = CALLBACK_ERROR;
            }
        }
        $this->update_callback_log($callback_log_id, $result);
        echo json_encode($result);
    }

    /**
     * Validate all request parameters and return the results
     *
     * @param $data
     *  The POST data of the callback call:
     * callbackKey is expected from where the callback is initialized
     */
    private function validate_callback($data, $userExists)
    {
        $result['groupId'] = -1;
        $result['selfhelpCallback'] = [];
        $result['callback_status'] = CALLBACK_SUCCESS;
        if (!isset($data['callbackKey']) || CALLBACK_KEY !== $data['callbackKey']) {
            //validation for the callback key; if wrong return not secured
            array_push($result['selfhelpCallback'], 'wrong callback key');
            $result['callback_status'] = CALLBACK_ERROR;
            return $result;
        }
        if (!isset($data['group']) || !isset($data['code'])) {
            // validation for required paramters; return parameters are missing            
            array_push($result['selfhelpCallback'], 'missing parameter: code or group');
            $result['callback_status'] = CALLBACK_ERROR;
            return;
        }
        if ($userExists) {
            $result['userId'] = $this->getUserId($data['code']);
            if ($result['userId'] == -1) {
                //validation for does the a user is registered with that code
                array_push($result['selfhelpCallback'], 'no user is registered with that code');
                $result['callback_status'] = CALLBACK_ERROR;
            }
        } else {
            if (!$this->doesCodeExists($data['code'])) {
                //validation for does the code exists in the validation codes table and it is not used
                array_push($result['selfhelpCallback'], 'code does not exist');
                $result['callback_status'] = CALLBACK_ERROR;
            }
        }
        $result['groupId'] = $this->getGroupId($data['group']);
        if (!($result['groupId'] > 0)) {
            // validation for does the group exists
            array_push($result['selfhelpCallback'], 'group does not exist');
            $result['callback_status'] = CALLBACK_ERROR;
        }
        return $result;
    }
}
?>
