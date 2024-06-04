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
    public function get_user_group($groupName, $id_users)
    {
        $key = $this->db->get_cache()->generate_key($this->db->get_cache()::CACHE_TYPE_CONDITION, $groupName, [__FUNCTION__, $id_users]);
        $get_result = $this->db->get_cache()->get($key);
        if ($get_result !== false) {
            return isset($get_result["result"]['group_name']);
        } else {
            $sql = "select g.name as group_name
                from users u
                inner join users_groups ug on (u.id = ug.id_users)
                inner join `groups` g on (ug.id_groups = g.id)
                where g.name = :group and u.id = :uid";
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
        return $this->db->get_user_language_id($id_users);
    }

    /**
     * Get the user last login date
     * @param $id_users
     * The id of the user
     * @return string
     * Return the last user login date
     */
    private function get_user_last_login_date($id_users){        
        return $this->db->get_user_last_login_date($id_users);
    }

    /**
     * Check if there is dynamic dates with structure __current_date__@-2 days, __current_date__@(https://www.php.net/manual/en/function.strtotime.php)
     * @param string $j_condition
     * The json condition string
     * @return string
     * Return the modified string
     */
    private function calculate_dynamic_dates($j_condition){
        // Define a regular expression pattern to match "__current_date__@-X month"
        $pattern = '/"__current_date__@(-\d+ days?)"/';

        // Find all matches in the input string
        preg_match_all($pattern, $j_condition, $matches);

        // Get the current date
        $currentDate = date('Y-m-d');

        // Iterate through the matches and replace with adjusted date
        foreach ($matches[0] as $match) {
            // Extract the number of months to adjust
            preg_match('/(-\d+) days?/', $match, $dayMatch);

            // Check if $dayMatch[1] is a valid integer
            if ($dayMatch && is_numeric($dayMatch[1])) {
                $daysToAdjust = (int)$dayMatch[1];
                // Ensure that the adjustment is within a reasonable range
                if ($daysToAdjust >= -1000 && $daysToAdjust <= 1000) {
                    // Calculate the new date
                    $newDate = date('Y-m-d',strtotime("$currentDate $daysToAdjust" . " days"));

                    // Replace the match with the new date
                    $j_condition = str_replace($match, "\"$newDate\"", $j_condition);
                    return $j_condition;
                }
            } else {
                // Handle invalid input
                // You may want to log an error or handle it differently based on your application's requirements
                return $j_condition;
            }
        }
        return $j_condition;
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
        $j_condition = $this->calculate_dynamic_dates($j_condition); // check for any dynamic dates __current_date__@-2 days
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
        if(strpos($j_condition, '__last_login__') !== false){
            $last_login = $this->get_user_last_login_date($id_users);
            $j_condition = str_replace('__last_login__', $last_login, $j_condition); // replace __last_login__
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
