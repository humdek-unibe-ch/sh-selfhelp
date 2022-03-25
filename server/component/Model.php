<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
/**
 * The class to define the basic functionality of a model.
 */
abstract class Model
{
    /* Private Properties *****************************************************/


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     */
    public function __construct()
    {
    }


    /**
     * Get params starting with $ fot the entry output
     * @param string $input
     * The field value that contain params
     * @retval array 
     * Array with all params in the field value
     */
    private function get_entry_param($input)
    {
        preg_match_all('~\$\w+\b~', $input, $m);
        $res = [];
        foreach ($m as $key => $value) {
            foreach ($value as $k => $param) {
                if ($param) {
                    $param_name = str_replace('$', '', $param);
                    $res[] = $param_name;
                }
            }
        }
        return $res;
    }

    /* Public Methods *********************************************************/

    /**
     * Get the value which is parsed with all params
     * @param array $entry_data
     * Array with the entry row
     * @param string value
     * The field value
     * @retval string
     * Return the value replaced with the params
     */
    public function get_entry_value($entry_data, $value)
    {
        $params = $this->get_entry_param($value);
        foreach ($params as $key => $param) {
            $value = isset($entry_data[$param]) ? str_replace('$' . $param, $entry_data[$param], $value) : $value; // if the param is not set, return the original
        }
        return $value;
    }
}
?>
