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
class ModuleFormsActionModel extends BaseModel
{

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        parent::__construct($services);
    }

    /**
     * get notifications from the database.
     *
     *  @retval array
     *  value int,
     *  text string
     */
    public function get_notifications()
    {
        $notifications = array();
        $sql = "SELECT id, name
                FROM formActions
                WHERE id_formActionScheduleTypes = :id_formActionScheduleTypes";
        $fetch_notifivations = $this->db->query_db($sql, array(
            ":id_formActionScheduleTypes" => $this->db->get_lookup_id_by_value(actionScheduleJobs, actionScheduleJobs_notification)
        ));
        foreach ($fetch_notifivations as $notification) {
            array_push($notifications, array("value" => $notification['id'], "text" => $notification['name']));
        }
        return $notifications;
    }

    public function get_jobs_groups($job_id)
    {
        $sql = "SELECT `name`
                FROM dta_jobs_groups jg
                INNER JOIN `groups` g ON g.id = jg.id_groups
                WHERE jg.id_jobs = :job_id;";
        $groups = $this->db->query_db($sql, array(":job_id" => $job_id));
        $res = array();
        foreach ($groups as $key => $value) {
            $res[] = $value['name'];
        }
        return $res;
    }

    public function test_show_action()
    {
        $id_action = 1;
        $action = $this->db->select_by_uid("dta_actions", $id_action);
        $config = array(
            "condition" => $this->db->select_by_uid("dta_conditions", $action['id_conditions']),
            "blocks" => $this->db->select_by_fk("dta_blocks", 'id_dta_actions', $id_action)
        );

        foreach ($config['blocks'] as $key_block => $block) {
            $jobs = $this->db->select_by_fk("dta_jobs", 'id_blocks', $block['id']);
            foreach ($jobs as $key_jobs => $job) {
                $jobs[$key_jobs]['condition'] = $this->db->select_by_uid("dta_conditions", $job['id_conditions']);
                $jobs[$key_jobs]['on_job_execute']['condition'] = $this->db->select_by_uid("dta_conditions", $job['id_conditions_on_execute']);
                $jobs[$key_jobs]['schedule_time'] = $this->db->select_by_uid("dta_schedule_time", $job['id_schedule_time']);
                $jobs[$key_jobs]['job_add_remove_groups'] = $this->get_jobs_groups($job['id']);
            }
            $config['blocks'][$key_block]['jobs'] = $jobs;
        }

        return json_encode($config, JSON_PRETTY_PRINT);
    }
}
