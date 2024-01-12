<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";
require_once __DIR__ . "/JsonLogic.php";
/**
 * This class is used to prepare all data related to the conditional container
 * component style such that the data can easily be displayed in the view of
 * the component.
 */
class ConditionalContainerModel extends StyleModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all profile related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the section with the conditional container style.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
    }

    /**
     * Check if the logged user is in this group.
     *
     * @param string $groupName
     *  group name that we want to check
     * @retval boolean
     *  returns true if the user belnogs to the group and false if not
     */
    private function get_user_group($groupName)
    {
        $sql = "select g.`name` as group_name
                from users u
                inner join users_groups ug on (u.id = ug.id_users)
                inner join `groups` g on (ug.id_groups = g.id)
                where g.`name` = :group and u.id = :uid";
        $res = $this->db->query_db_first($sql, array(
            ':group' => $groupName,
            ':uid' => $_SESSION['id_user']
        ));
        return  isset($res['group_name']);
    }

    /* Public Methods *********************************************************/

    /**
     * Use the JsonLogic libarary to compute whether the json condition is true
     * or false.
     *
     * @param array $condition
     *  An array representing the json condition string.
     * @retval mixed
     *  The evaluated condition.
     */
    public function compute_condition($condition)
    {
        $res = array("result" => false, "fields" => array());
        if ($condition === null || $condition === "")
            return true;
        $j_condition = json_encode($condition);
        // replace form field keywords with the actual values.
        $pattern = '~"' . $this->user_input->get_input_value_pattern() . '"~';
        preg_match_all($pattern, $j_condition, $matches, PREG_PATTERN_ORDER);
        foreach ($matches[0] as $match) {
            $val = $this->user_input->get_input_value_by_pattern(trim($match, '"'), $_SESSION['id_user']);
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
            $val = $this->get_user_group($groupName);
            $res['group_name'] = $val;
            $j_condition = str_replace($match, '"' . $val . '"', $j_condition);
        }
        // compute the condition
        try
        {
            $res['result'] = JsonLogic::apply(json_decode($j_condition, true));
        }
        catch(\Exception | \ArgumentCountError $e)
        {
            $res['fields'] = "JsonLogic::apply() failed in section '"
                . $this->get_db_field('id') . "': " . $e->getMessage();
        }

        return $res;
    }
}
?>
