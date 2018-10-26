<?php
require_once __DIR__ . "/../StyleModel.php";

/**
 * This class is used to prepare all data related to the userData style
 * components such that the data can easily be displayed in the view of the
 * component.
 */
class ShowUserInputModel extends StyleModel
{
    /* Private Properties *****************************************************/

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
     * Get the input data of the current user from the database of a given from.
     *
     * @param string $form_name
     *  The name of the form from which the data will be fetched.
     * @retval array
     *  See UserInput::fetch_input_fields()
     */
    public function get_user_data($form_name)
    {
        return $this->user_input->get_input_fields(array(
            "form_name" => $form_name,
            "id_user" => $_SESSION['id_user'],
        ));
    }
}
?>
