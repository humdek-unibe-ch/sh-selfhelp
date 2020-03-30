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
     * Checks whether the current user is allowed to delete user data.
     *
     * @retval bool
     *  True if the current user can delete user data, false otherwise.
     */
    public function can_delete_user_data()
    {
        return $this->acl->has_access_delete($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("exportDelete"));
    }

    /**
     * Checks whether the current user is allowed to export validation codes.
     *
     * @retval bool
     *  True if the current user can export validation codes, false otherwise.
     */
    public function can_export_codes()
    {
        return $this->acl->has_access_select($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("userSelect"));
    }

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
                    "label" => "Get User Input Data",
                    "type" => "primary",
                ),
            );
            if($this->can_delete_user_data())
                $fields['options'][] = array(
                    "url" => $this->get_link_url("exportDelete",
                        array("selector" => "user_input")),
                    "label" => "Remove User Input Data",
                    "type" => "danger",
                );
        }
        if($selector === "user_input_form")
        {
            $fields["title"] = "User Input Form";
            $fields["text"] = "Export data for a signle form";
            $fields["form"] = true;
            $fields["options"] = [];
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
            if($this->can_delete_user_data())
                $fields['options'][] = array(
                    "url" => $this->get_link_url("exportDelete",
                        array("selector" => "user_activity")),
                    "label" => "Remove User Activity",
                    "type" => "danger",
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
                    "label" => "Get Consumed Validation Codes",
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
               where style_group = "Form" and style_type = "component"';
        return $this->db->query_db($sql);
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

    /**
     * Get the export link
     *
     * @param post $params
     */
    public function get_user_export_form_url($params)
    {       
        return $this->router->generate('exportData', $params);
    }
}
?>
