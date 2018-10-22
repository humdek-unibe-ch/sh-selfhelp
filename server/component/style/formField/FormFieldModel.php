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
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
    }

    /* Public Methods *********************************************************/

    /**
     * If the parent form is of style formDoc fetch the last unser input value
     * for this form field from the database, otherwise get the default value.
     *
     * @retval string
     *  The form field value.
     */
    public function get_form_field_value()
    {
        if(!$this->show_db_value) return $this->get_db_field("value");
        $fields = $this->user_input->get_input_fields(array(
            "id_section" => $this->get_db_field('id'),
            "id_user" => $_SESSION['id_user'],
        ));
        $field_count = count($fields);
        if($fields && $field_count > 0)
            return $fields[$field_count - 1]['value'];
        else
            return "";
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
}
?>
