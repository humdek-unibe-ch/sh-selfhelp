<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the export component such
 * that the data can easily be displayed in the view of the component.
 */
class ExportModel extends BaseModel
{
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
     * Returns the view data of an export item.
     *
     * @param string $selector
     *  The key to select the chosen data.
     * @retval array
     *  An array holding the informaition to render the view of an export item.
     *  The following keys are used:
     *   'title':   The title of the item.
     *   'text':    The description of the item.
     *   'url':     The target url of the item.
     *   'label':   The label of the button to download the item.
     */
    public function get_export_view_fields($selector)
    {
        $fields = array(
            "title" => "unknown",
            "text" => "unknown",
            "url" => "#",
            "label" => "unknown",
        );
        if($selector === "user_input")
        {
            $fields["title"] = "User Input";
            $fields["text"] = "The collection of all data that was entered by users through a form field. Only form fields from pages marked as user input pages are considered. Each item was timestamped at the time of creation.";
            $fields["options"] = array(
                array(
                    "url" => $this->get_link_url("exportData",
                            array("selector" => "user_input")),
                    "label" => "Get User Data",
                    "type" => "primary",
                ),
            );
        }
        if($selector === "user_activity")
        {
            $fields["title"] = "User Activity";
            $fields["text"] = "The collection of all user activity on experiment pages.";
            $fields["options"] = array(
                array(
                    "url" => $this->get_link_url("exportData",
                        array("selector" => "user_activity")),
                    "label" => "Get User Activity",
                    "type" => "primary",
                ),
            );
        }
        if($selector === "validation_codes")
        {
            $fields["title"] = "Validation Codes";
            $fields["text"] = "The list of valid validation codes users can use to register.";
            $fields["options"] = array(
                array(
                    "url" => $this->get_link_url("exportData",
                        array("selector" => "validation_codes", "option" => "all")),
                    "label" => "Get All Validation Codes",
                    "type" => "primary",
                ),
                array(
                    "url" => $this->get_link_url("exportData",
                        array("selector" => "validation_codes", "option" => "used")),
                    "label" => "Get Used Validation Codes",
                    "type" => "warning",
                ),
                array(
                    "url" => $this->get_link_url("exportData",
                        array("selector" => "validation_codes", "option" => "open")),
                    "label" => "Get Open Validation Codes",
                    "type" => "success",
                ),
            );
        }
        return $fields;
    }

    /**
     * Get the header of the export page.
     *
     * @retval string
     *  The export page header.
     */
    public function get_title()
    {
        return "Data Export";
    }

    /**
     * Get the description of the export page.
     *
     * @retval string
     *  The description of the export page.
     */
    public function get_text()
    {
        $txt = "Export experiment related data from the data base as CSV file. ";
        $txt .= "Note that the user id is obfuscated with a hash function to assure annonimity";
        return $txt;
    }
}
?>
