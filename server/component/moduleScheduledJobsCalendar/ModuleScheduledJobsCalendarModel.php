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
class ModuleScheduledJobsCalendarModel extends BaseModel
{

    /* Constructors ***********************************************************/

    /* Private Properties *****************************************************/
    /**
     * selected user,
     */
    private $uid;

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $uid
     * The selected user
     */
    public function __construct($services, $uid)
    {
        parent::__construct($services);
        $this->uid = $uid;
    }

    /**
     * Get selected user
     * @return int
     * Return the user id
     */
    public function get_selected_user()
    {
        return $this->uid;
    }

    /**
     * Set selected user
     * @param int $uid
     * The selected user id
     */
    public function set_selected_user($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Get all users;
     * @return array
     * array used for select dropdown
     */
    public function get_users()
    {
        $arr = array();
        $sql = "SELECT id, email, code, name 
                FROM users u 
                LEFT JOIN validation_codes c on (c.id_users = u.id)
                WHERE id_status = :active_status";
        $users = $this->db->query_db($sql, array(':active_status' => USER_STATUS_ACTIVE));
        foreach ($users as $val) {
            array_push($arr, array("value" => intval($val['id']), "text" => ("[" . $val['code'] . '] ' . $val['email']) . ' - ' . $val['name']));
        }
        return $arr;
    }

    /**
     * Get the selected events based on the selected user.
     * If no user return empty array
     * @return array
     * Return the events in array
     */
    public function get_scheduled_events()
    {
        if ($this->uid > 0) {
            $scheduled_job_url = $this->get_link_url("moduleScheduledJobs", array("sjid" => ":sjid"));
            $sql = "SELECT sj.id, l_status.lookup_code AS status_code, 
                    CASE 
                        WHEN l_types.lookup_code = 'task' THEN JSON_UNQUOTE(JSON_EXTRACT(t.config, '$.type'))
                        ELSE l_types.lookup_code 
                    END AS type_code
                    , `description` as title,
                    date_to_be_executed AS `start`, DATE_ADD(date_to_be_executed, INTERVAL 1 SECOND) AS `end`, :sjid AS `url`
                    FROM scheduledJobs sj
                    INNER JOIN scheduledJobs_users sju ON (sj.id = sju.id_scheduledJobs)
                    INNER JOIN lookups l_status ON (l_status.id = sj.id_jobStatus)
                    INNER JOIN lookups l_types ON (l_types.id = sj.id_jobTypes)
                    LEFT JOIN scheduledJobs_tasks sjt ON (sjt.id_scheduledJobs = sj.id)
                    LEFT JOIN tasks t ON (sjt.id_tasks = t.id)
                    WHERE sju.id_users = :uid";
            return $this->db->query_db($sql, array(
                ':uid' => $this->uid,
                ":sjid" => $scheduled_job_url
                ));
        } else {
            return array();
        }
    }
}
