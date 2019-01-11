<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the register component such
 * that the data can easily be displayed in the view of the component.
 */
class RegisterModel extends StyleModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The section id of the register component instance.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);

        $fields = $this->db->fetch_section_fields($id);
        $this->set_db_fields($fields);
    }

    /* Public Methods *********************************************************/

    /**
     * A simple wrapper for the credential check in the login service.
     *
     * @param string $email
     *  The email address of the user.
     * @param string $code
     *  The code string entered by the user.
     * @retval bool
     *  true if the check was successful, false otherwise.
     */
    public function check_register_credentials($email, $code)
    {
    }
}
?>
