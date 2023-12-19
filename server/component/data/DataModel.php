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
     *   - 'form_id':    form_id used as combobox value and used as a paramter for the databse function to retrieve the data.
     *   - 'form_name':  form name shown in the combo box
     */
    public function get_forms()
    {
        // log user activity on export pages
        $this->services->get_db()->insert("user_activity", array(
            "id_users" => $_SESSION['id_user'],
            "url" => $_SERVER['REQUEST_URI'],
            "id_type" => 2,
        ));
        $sql = 'select cast(s.id as unsigned) as form_id, ifnull(sft_if.content, s.`name`) as form_name 
                from sections s
                inner join view_styles st on (s.id_styles = st.style_id)
                LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = s.id AND sft_if.id_fields = 57
                where style_group = "Form" and style_type = "component" and style_name <> "showUserInput"
                order by form_name';
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
        if ($user_ids == 'all') {
            // if no user is selected return data for all
            $sql = 'call get_form_data(' . $formId . ')';
            return $this->services->get_db()->query_db($sql);
        } else {
            //return for the selected user
            $sql = 'call get_form_data_for_user(' . $formId . ', ' . $user_ids . ')';
            return $this->services->get_db()->query_db($sql);            
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
                FROM `users` u 
                LEFT JOIN validation_codes c on (c.id_users = u.id)
                WHERE id_status = :active_status";
        $users = $this->db->query_db($sql, array(':active_status' => USER_STATUS_ACTIVE));
        array_push($arr, array(
                "value" => 'all',
                "text" => 'All',
            ));
        foreach ($users as $val) {
            $value = ('user_' . intval($val['id']));
            //$selected = $this->users && array_search($value, $this->users) !== false ? 'selected' : '';
            array_push($arr, array(
                "value" => $value,
                "text" => ("[" . $val['code'] . '] ' . $val['email']) . ' - ' . $val['name'],
              //  "selected" => $selected
            ));
        }
        return $arr;
    }

    /**
     * Get all groups;
     * @retval array
     * array used for select dropdown
     */
    public function get_groups()
    {
        $arr = array();
        $sql = "SELECT id, name 
                FROM `groups`;";
        $groups = $this->db->query_db($sql);
        foreach ($groups as $val) {
            $value = ('group_' . intval($val['id']));
            $selected = $this->users && array_search($value, $this->users) !== false ? 'selected' : '';
            array_push($arr, array(
                "value" => $value,
                "text" => $val['name'],
                "selected" => $selected
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
        return str_replace('user_', '', $this->users);
    }

    public function get_selected_forms()
    {
        return $this->forms;
    }
}
?>
