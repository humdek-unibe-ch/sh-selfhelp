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
     * @param mixed
     *  The evaluated condition.
     */
    public function compute_condition($condition)
    {
        if($condition === null || $condition === "")
            return true;
        $j_condition = json_encode($condition);
        // replace form field keywords with the actual values.
        preg_match_all('~"@\w+#\w+"~', $j_condition, $matches, PREG_PATTERN_ORDER);
        foreach($matches[0] as $match)
        {
            $names = explode('#', trim($match, '"'));
            if(count($names) !== 2)
                continue;
            $form = substr($names[0], 1);
            $field = $names[1];
            $vals = $this->user_input->get_input_fields(array(
                "form_name" => $form,
                "field_name" => $field,
                "id_user" => $_SESSION['id_user']
            ));
            if(count($vals) > 0)
                $j_condition = str_replace($match, $vals[0]['value'], $j_condition);
        }
        // compute the condition
        return JsonLogic::apply(json_decode($j_condition, true));
    }

}
?>
