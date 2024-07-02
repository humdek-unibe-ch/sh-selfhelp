<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class CacheModel extends BaseModel
{
    /* Private Properties *****************************************************/

    /**
     * The constructor
     *
     * @param object $services
     *  An associative array holding the different available services. See the
     *  class definition base page for a list of all services.
     * @param int $id
     *  The id of the section with the conditional container style.
     * @param array $params
     *  The list of get parameters to propagate.     
     */
    public function __construct($services, $id, $params)
    {
        parent::__construct($services, $id, $params);
    }

    /* Private Methods *********************************************************/


    /* Public Methods *********************************************************/
    /**
     * Clears the cache based on POST data.
     *
     * This function iterates through the POST data and checks if the value for each key is 1.
     * If the key matches the constant for clearing all cache, it clears the entire cache and 
     * returns a success message. Otherwise, it clears the cache for the specific key and 
     * appends a success message to the result array.
     *
     * @return array An array of messages indicating which caches were successfully cleared.
     */
    public function clear_cache()
    {
        $msgs = [];
        foreach ($_POST as $key => $value) {
            if ($value == 1) {
                if ($key == $this->db->get_cache()::CACHE_ALL) {
                    $this->db->clear_cache();
                    return ['Successfully cleared all the cache!'];
                } else {
                    $this->db->clear_cache($key);
                    $msgs[] = 'Successfully cleared ' . $key;
                }
            }
        }
        return $msgs;
    }
}
