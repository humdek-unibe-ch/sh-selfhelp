<?php
require_once __DIR__ . "/../StyleModel.php";
/**
 * This class is used to prepare all data related to the profile component such
 * that the data can easily be displayed in the view of the component.
 */
class ProfileModel extends StyleModel
{
    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all profile related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The id of the section to which this style is assigned.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
    }

    /**
     * Delete the active user if the given email address matches with the email
     * address of the current user. If the deletion of the user was successful,
     * logout the current user (which was just deleted).
     *
     * @param string $email
     *  The user email address.
     * @retval bool
     *  True if the deleting process was successful, false otherwise.
     */
    public function delete_user($email)
    {
        $res = $this->login->delete_user($_SESSION['id_user'], $email);
        if($res) $this->login->logout();
        return $res;
    }

    /**
     * Change the user name of the active user in the database.
     *
     * @param string $name
     *  The new username
     * @retval bool
     *  True on success, false otherwise.
     */
    public function change_user_name($name)
    {
        $res = $this->db->update_by_ids('users', array("name" => $name),
            array('id' => $_SESSION['id_user']));
        if($res)
        {
            $input = $this->get_child_section_by_name('profile-username-input');
            $input = $input->get_style_instance();
            $input->update_value_view($name);
            return true;
        }
        return false;
    }

    /**
     * Change the password of the active user.
     *
     * @param string $password
     *  The new password.
     * @param string $verification
     *  A seperate string that must match the new password.
     * @retval bool
     *  True if the change was successful, false otherwise
     */
    public function change_password($password, $verification)
    {
        if($password != $verification) return false;
        return $this->login->change_password($password);
    }

    /**
     * Propagate the remindier setting to the user table. This is necessary
     * beacuse the reminder settings are stored as user data while the reminder
     * script checks the is_reminded falg in the users table.
     */
    public function update_user_reminder_settings()
    {
        $field_reminder = $this->user_input->get_input_fields(array(
            'page' => 'profile',
            'id_user' => $_SESSION['id_user'],
            'form_name' => 'notification',
            'field_name' => 'reminder',
        ));
        $val = 0;
        if(count($field_reminder) === 0 || $field_reminder[0]['value'] !== "")
            $val = 1;
        $this->db->update_by_ids('users', array('is_reminded' => $val),
            array('id' => $_SESSION['id_user']));
    }
}
?>
