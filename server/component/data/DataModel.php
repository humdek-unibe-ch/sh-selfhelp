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
        if ($user_ids == 'all') {
            // add limit or the table will not load if too much data is shown
            return $this->user_input->get_data($formId, '', false);
        } else {
            return $this->user_input->get_data_for_user($formId, $user_ids, '');
        }
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
