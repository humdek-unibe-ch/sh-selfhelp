<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the export component such
 * that the data can easily be displayed in the view of the component.
 */
class ExportDeleteModel extends BaseModel
{
    /* Private Properties *****************************************************/

    /**
     * The string to identify which data to remove.
     */
    private $selector = "";

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $selector
     *   The string to identify which data to remove.
     */
    public function __construct($services, $selector)
    {
        $this->selector = $selector;
        parent::__construct($services);
    }

    /* Public Methods *********************************************************/

    /**
     * Return the selector passed by url.
     *
     * @retval string
     *  The selector to decide which data to clear.
     */
    public function get_selector()
    {
        return $this->selector;
    }

    /**
     * Return the description text of the data to clear.
     *
     * @retval string
     *  A markdown string holding the description of what data is going to be
     *  removed.
     */
    public function get_text()
    {
        $warn = "\n\nTypically you would want to perform this operation after a pilot study or after testing to start with a clean database. **This operation cannot be undone**.";
        if($this->selector === "user_activity")
            return "This will remove all activity logs of every user from the database." . $warn;
        else if($this->selector === "user_input")
            return "This will remove all input data of every user from the database. Input data is all data that was entered by this user through either a style of type formUserInput, mermaidForm, as well as all information entered during the validation process and in the settings of the user profile (except name, password, and gender)." . $warn;
        else
            return "unknown selector '" . $this->selector . "'";
    }

    /**
     * Return the data description title.
     *
     * @retval string
     *  A title postfix to indicate the data which will be removed.
     */
    public function get_title()
    {
        if($this->selector === "user_activity")
            return "Activity";
        else if($this->selector === "user_input")
            return "Input Data";
        else
            return "unknown selector '" . $this->selector . "'";
    }

    /**
     * Return the veryfication string.
     *
     * @retval string
     *  A constant string which is used to verify whether a user really means to
     *  delete all data.
     */
    public function get_veryfication_str()
    {
        return "delete";
    }

    /**
     * Executes the db query to remove the data.
     *
     * @retval misxed
     *  The number of rows deleted or false on failure.
     */
    public function remove_all_data()
    {
        if($this->selector === "user_activity")
            $sql = "DELETE FROM user_activity";
        else if($this->selector === "user_input")
            $sql = "DELETE FROM user_input";
        else
            return false;
        return $this->db->execute_db($sql);
    }
}
?>
