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
        if($condition === null || $condition === "")
            return true;
        $j_condition = json_encode($condition);
        // replace form field keywords with the actual values.
        $pattern = '~"' . $this->user_input->get_input_value_pattern() . '"~';
        preg_match_all($pattern, $j_condition, $matches, PREG_PATTERN_ORDER);
        foreach($matches[0] as $match)
        {
            $val = $this->user_input->get_input_value_by_pattern(trim($match, '"'), $_SESSION['id_user']);
            if($val === null)
                $res['fields'][$match] = "bad field syntax";
            else if($val === "")
                $res['fields'][$match] = "no value stored for this field";
            else
            {
                $res['fields'][$match] = $val;
                $j_condition = str_replace($match, '"'.$val.'"', $j_condition);
            }
        }
        // compute the condition
        $res['result'] = JsonLogic::apply(json_decode($j_condition, true));
        return $res;
    }

}
?>
