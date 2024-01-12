<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../../component/style/conditionalContainer/JsonLogic.php";

class BasicJob
{

    /**
     * The db instance which grants access to the DB.
     */
    protected $db;

    /**
     * The transaction instance that log to DB.
     */
    protected $transaction;

    /**
     * Creating a PHPMailer Instance.
     *
     * @param object $db
     *  An instcance of the service class PageDb.
     */
    public function __construct($db, $transaction)
    {
        $this->db = $db;
        $this->transaction = $transaction;
    }

    /**
     * Check if the logged user is in this group.
     *
     * @param string $groupName
     *  group name that we want to check
     * @retval boolean
     *  returns true if the user belnogs to the group and false if not
     */
    private function get_user_group($groupName, $id_users)
    {
        $sql = "select g.`name` as group_name
                from `users` u
                inner join users_groups ug on (u.id = ug.id_users)
                inner join `groups` g on (ug.id_groups = g.id)
                where g.`name` = :group and u.id = :uid";
        $res = $this->db->query_db_first($sql, array(
            ':group' => $groupName,
            ':uid' => $id_users
        ));
        return  isset($res['group_name']);
    } 

    protected function check_condition($condition, $id_users)
    {
        if ($condition === null || $condition === "")
            return true;
        $j_condition = $condition;

        preg_match_all('~"\$[^"@#]+"~', $j_condition, $matches, PREG_PATTERN_ORDER); // group pattern
        foreach ($matches[0] as $match) {
            $groupName = trim($match, '"');
            $groupName = str_replace("$", "", $groupName);
            $val = $this->get_user_group($groupName, $id_users);
            $res['group_name'] = $val;
            $j_condition = str_replace($match, '"' . $val . '"', $j_condition);
        }
        // compute the condition
        try {
            return JsonLogic::apply(json_decode($j_condition, true));
        } catch (\Exception | \ArgumentCountError $e) {
            return false;
        }
    }

}

?>