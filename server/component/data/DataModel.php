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
     * Get the all generated forms from the users in the cms
     *
     * @retval array
     *  As array of items where each item has the following keys:
     *   - 'form_id':    form_id used as combobox value and used as a parameter for the database function to retrieve the data.
     *   - 'form_name':  form name shown in the combo box
     */
    public function get_forms()
    {
        $sql = 'SELECT type, id AS form_id, orig_name AS form_name, form_id_plus_type AS form, internal
                FROM view_data_tables
                WHERE internal <> 1';
        return $this->db->query_db($sql);
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
        $formInfo = explode('-', $formId);
        $formId = $formInfo[0];
        $formType = $formInfo[1];
        if ($user_ids == 'all') {
            // add limit or the table will not load if too much data is shown
            return $this->user_input->get_data($formId, 'LIMIT 0, 10000', false, $formType);
        } else {
            return $this->user_input->get_data_for_user($formId, $user_ids, '', $formType);
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
            //$selected = $this->users && array_search($value, $this->users) !== false ? 'selected' : '';
            array_push($arr, array(
                "value" => $value,
                "text" => ("[" . $val['code'] . '] ' . $val['email']) . ' - ' . $val['name'],
                //  "selected" => $selected
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
