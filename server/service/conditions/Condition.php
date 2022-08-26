<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/JsonLogic.php";
/**
 * This class allows to check different condtions based on JSON logic.
 */
class Condition
{
    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /**
     * The db instance which grants access to the user input.
     */
    private $user_input;

    /**
     * The router instance which is used to generate valid links.
     */
    private $router;

    /**
     * Start the session.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param object $user_input
     *  The user_input instance which grants access to the user_input.
     */
    public function __construct($db, $user_input, $router)
    {
        $this->db = $db;
        $this->user_input = $user_input;
        $this->router = $router;
        $this->db->get_cache()->clear_cache($this->db->get_cache()::CACHE_TYPE_CONDITION);
    }

    /**
     * Check if the logged user is in this group.
     *
     * @param string $groupName
     *  group name that we want to check
     * @param int $id_users
     * the user who we will check if is in the group
     * @retval boolean
     *  returns true if the user belnogs to the group and false if not
     */
    private function get_user_group($groupName, $id_users)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_CONDITION, $groupName, [__FUNCTION__, $id_users]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return isset($get_result["result"]['group_name']);
        } else {
            $sql = 'select g.name as group_name
                from users u
                inner join users_groups ug on (u.id = ug.id_users)
                inner join "groups" g on (ug.id_groups = g.id)
                where g.name = :group and u.id = :uid';
            $res = $this->db->query_db_first($sql, array(
                ':group' => $groupName,
                ':uid' => $id_users
            ));
            $this->db->get_cache()->set($key, array("result"=>$res));
            return isset($res['group_name']);
        }  
    }

    /**
     * Get the user selected language
     * @param $id_users
     * The id of the user
     * @return int
     * Return the saved language id or false if not found
     */
    private function get_user_language_id($id_users){
        $sql = "SELECT id_languages
                FROM users u                
                WHERE u.id = :uid";
        $res = $this->db->query_db_first($sql, array(
            ':uid' => $id_users
        ));
        return isset($res['id_languages']) ? $res['id_languages'] : false;
    }

    /**
     * Use the JsonLogic libarary to compute whether the json condition is true
     * or false.
     *
     * @param array $condition
     *  An array representing the json condition string.
     * @param int $id_users
     * the user who we will check if is in the group. If not set then the session logged user
     * @param string $section
     * the name of the section that we checked, if it is not form section then we specify
     * @retval mixed
     *  The evaluated condition.
     */
    public function compute_condition($condition, $id_users = null, $section = 'system')
    {
        if(!$condition){
            // there is no condition to check just go on
            $res['result'] = true;            
            return $res;
        }
        $id_users = $id_users ? $id_users : $_SESSION['id_user']; // set default value to $_SESSION['id_user'] if not set
        $res = array("result" => false, "fields" => array());
        if ($condition === null || $condition === "")
            return true;
        $j_condition = json_encode($condition);
        $j_condition = str_replace('__current_date__', date('Y-m-d'), $j_condition); // replace __current_date__
        $j_condition = str_replace('__current_date_time__', date('Y-m-d H:i'), $j_condition); // replace __current_date_time__
        $j_condition = str_replace('__current_time__', date('H:i'), $j_condition); // replace __current_time__
        $platform = (isset($_POST['mobile']) && $_POST['mobile']) ? pageAccessTypes_mobile : pageAccessTypes_web;
        $j_condition = str_replace('__platform__', $platform, $j_condition); // replace platform
        $keyword = $this->router->get_keyword_from_url();
        $j_condition = str_replace('__keyword__', $keyword, $j_condition); // replace __keyword__
        if(strpos($j_condition, '__language__') !== false){
            $language = $this->get_user_language_id($id_users);
            $j_condition = str_replace('__language__', $language, $j_condition); // replace __language__
        }
        // replace form field keywords with the actual values.
        $pattern = '~"' . $this->user_input->get_input_value_pattern() . '"~';
        preg_match_all($pattern, $j_condition, $matches, PREG_PATTERN_ORDER);
        foreach ($matches[0] as $match) {
            $val = $this->user_input->get_input_value_by_pattern(trim($match, '"'), $id_users);
            if ($val === null) {
                $res['fields'][$match] = "bad field syntax";
            }
            else if ($val === "") {
                $res['fields'][$match] = "no value stored for this field";
            }
            else {
                $res['fields'][$match] = $val;
            }
            $j_condition = str_replace($match, '"' . $val . '"', $j_condition);
        }

        preg_match_all('~"\$[^"@#]+"~', $j_condition, $matches, PREG_PATTERN_ORDER); // group pattern
        foreach ($matches[0] as $match) {
            $groupName = trim($match, '"');
            $groupName = str_replace("$", "", $groupName);
            $val = $this->get_user_group($groupName, $id_users);
            $res['group_name'] = $val;
            $j_condition = str_replace($match, '"' . $val . '"', $j_condition);
        }
        // compute the condition
        try
        {
            $j_condition = str_replace("\\", '\\\\', $j_condition); //get rid of new lines, otherwise it fails
            $j_condition = str_replace("\r\n", '\n', $j_condition); //get rid of new lines, otherwise it fails
            $j_condition = json_decode($j_condition, true);
            $res['result'] = JsonLogic::apply($j_condition);
        }
        catch(\Exception | \ArgumentCountError $e)
        {
            $res['fields'] = "JsonLogic::apply() failed in section '"
                . $section . "': " . $e->getMessage();
        }

        return $res;
    }
}
?>
