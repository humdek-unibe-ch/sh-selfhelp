<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
//require_once __DIR__ . "/../BaseModel.php";
require_once __DIR__ . "/../User/UserModel.php";
/**
 * This class is used to prepare all data related to the data component such
 * that the data can easily be displayed in the view of the component.
 */
class DataModel extends UserModel
{

    /* Private Properties *****************************************************/

    /**
     * The selected user id.
     */
    private $uid;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $uid
     *  The selected user id.
     */
    public function __construct($services, $uid = null)
    {
        parent::__construct($services, $uid);
        $this->uid = $uid;
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
        $sql = 'select cast(s.id as unsigned) as form_id, sft_if.content as form_name 
               from sections s
               inner join view_styles st on (s.id_styles = st.style_id)
               LEFT JOIN sections_fields_translation AS sft_if ON sft_if.id_sections = s.id AND sft_if.id_fields = 57
               where style_group = "Form" and style_type = "component" and style_name <> "showUserInput"
               order by sft_if.content';
        return $this->db->query_db($sql);
    }

    /**
     * Get the all fields from a form
     *
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
    public function getFormFields($formId)
    {
        if ($this->uid) {
            //return for the selected user
            $sql = 'call get_form_data_for_user(' . $formId . ', ' . $this->uid . ')';
            return $this->services->get_db()->query_db($sql);
        } else {
            // if no user is selected return data for all
            $sql = 'call get_form_data(' . $formId . ')';
            return $this->services->get_db()->query_db($sql);
        }
    }    
}
?>