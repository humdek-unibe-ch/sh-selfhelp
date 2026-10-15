<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the data component such
 * that the data can easily be displayed in the view of the component.
 */
class DataModel extends BaseModel
{

    /* Private Properties *****************************************************/

    /**
     * Selected uesers
     */
    private $users = array();

    /**
     * Selected forms
     */
    private $forms;

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

    /* Public Methods *********************************************************/

    /**
     * Get the all dataTabled
     *
     * @return array
     *  As array of items where each item has the following keys:
     *   - 'id':    dataTable id
     *   - 'name':  dataTable name
     *   - 'displayName': dataTable displayName
     */
    public function get_dataTables()
    {
        return $this->user_input->get_dataTables();
    }

    /**
     * Build comma-separated dataCols for Admin Data, skipping names that start
     * with `_` (e.g. `_json`, large SurveyJS payloads). Those columns are
     * still stored; they are only omitted from the admin grid query so the
     * page does not pull huge cell values into HTML.
     *
     * @param int $formId
     * @return string
     */
    private function get_admin_data_selected_columns($formId)
    {
        $rows = $this->db->query_db(
            "SELECT `name` FROM dataCols
             WHERE id_dataTables = :id
               AND LEFT(`name`, 1) <> '_'
               AND `name` REGEXP '^[a-zA-Z0-9_]+$'",
            array(':id' => $formId)
        );
        if (!$rows) {
            // Sentinel: FIND_IN_SET matches nothing → no pivoted payload cols.
            return '__admin_skip_underscore__';
        }
        $selected = '';
        foreach ($rows as $row) {
            $name = $row['name'];
            $next = ($selected === '') ? $name : ($selected . ',' . $name);
            // selected_columns_param is VARCHAR(4000) in get_dataTable_with_filter
            if (strlen($next) > 3900) {
                break;
            }
            $selected = $next;
        }
        return $selected !== '' ? $selected : '__admin_skip_underscore__';
    }

    /**
     * Get the all fields from a form
     * @param int $formId
     * form id
     * @param string $user_ids
     * user ids  
     * @retval array
     *  As array of items where each item has the following keys:
     *   - 'user_id'
     *   - 'form_name'
     *   - 'edit_time'
     *   - 'user_name'
     *   - 'user_code'
     *   -  many fileds depending on the form 
     *   -  many fileds depending on the form 
     *   -  and so on
     *   - 'deleted'
     */
    public function getFormFields($formId, $user_ids)
    {
        $selected_columns = $this->get_admin_data_selected_columns($formId);
        if ($user_ids == 'all') {
            // add limit or the table will not load if too much data is shown
            return $this->user_input->get_data($formId, '', false, -1, false, false, $selected_columns);
        }
        return $this->user_input->get_data($formId, '', false, $user_ids, false, false, $selected_columns);
    }

    /**
     * Get all active users;
     * @retval array
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
        array_push($arr, array(
            "value" => 'all',
            "text" => 'All',
        ));
        foreach ($users as $val) {
            $value = (intval($val['id']));
            array_push($arr, array(
                "value" => $value,
                "text" => ("[" . $val['code'] . '] ' . $val['email']) . ' - ' . $val['name'],
            ));
        }
        return $arr;
    }

    public function set_selected_users($users)
    {
        $this->users = $users;
    }

    public function set_selected_forms($forms)
    {
        $this->forms = $forms;
    }

    public function get_selected_users()
    {
        return $this->users;
    }

    public function get_selected_forms()
    {
        return $this->forms;
    }
}
?>
