<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";

/**
 * This class is used to prepare all data related to the formField style
 * component such that the data can easily be displayed in the view of the
 * component.
 */
class FormFieldModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * If set to true, the latest data from the database is displayed in the
     * form field. If set to false the default values are displayed if defined.
     */
    private $show_db_value = false;

    /**
     * If set to true a form field is prepared to synchronoize its content with
     * the database. If set to false, a normal form field is rendered and any
     * interaction with the database has to be made manually.
     */
    private $is_user_input = false;

    /**
     * The id of the form
     */
    private $form_id = null;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        parent::__construct($services, $id, $params, $id_page, $entry_record);
    }

    /* Public Methods *********************************************************/

    /**
     * Fetch the form field value from the database if it exists.
     *
     * @retval string
     *  The form field value.
     */
    public function get_form_field_value()
    {
        $fields = $this->user_input->get_input_fields(array(
            "id_section" => $this->get_db_field('id'),
            "id_user" => $_SESSION['id_user'],
            "id_section_form" => $this->form_id
        ));
        $field_count = count($fields);
        if($fields && $field_count > 0)
            return $fields[$field_count - 1]['value'];
        else
            return null;
    }

    /**
     * Getter for FormFieldModel::is_user_input.
     *
     * @retval bool
     *  See FormFieldModel::is_user_input.
     */
    public function get_user_input()
    {
        return $this->is_user_input;
    }

    /**
     * Getter for FormFieldModel::show_db_value.
     *
     * @retval bool
     *  See FormFieldModel::show_db_value.
     */
    public function get_show_db_value()
    {
        return $this->show_db_value;
    }

    /**
     * Set form_id
     * @param $form_id integer
     * the id of the form
     */
    public function set_form_id($form_id)
    {
        $this->form_id = $form_id;
    }

    /**
     * Setter for FormFieldModel::show_db_value.
     *
     * @param bool $val
     *  See FormFieldModel::show_db_value.
     */
    public function set_show_db_value($val)
    {
        $this->show_db_value = $val;
    }

    /**
     * Setter for FormFieldModel::is_user_input.
     *
     * @param bool $val
     *  See FormFieldModel::is_user_input.
     */
    public function set_user_input($val)
    {
        $this->is_user_input = $val;
    }
}
?>
