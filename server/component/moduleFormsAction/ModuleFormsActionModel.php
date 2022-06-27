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
     * Get the all generated forms from the users in the cms
     *
     * @retval array
     *  As array of items where each item has the following keys:
     *   - 'form_id':    form_id used as combobox value and used as a paramter for the databse function to retrieve the data.
     *   - 'form_name':  form name shown in the combo box
     */
    public function get_forms()
    {
        $surveys = array();
        foreach ($this->db->select_table("view_form") as $survey) {
            array_push($surveys, array("value" => $survey['form_id'], "text" => $survey['form_name']));
        }
        return $surveys;
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

}