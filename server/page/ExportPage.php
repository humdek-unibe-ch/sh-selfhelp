<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/../service/UserInput.php";

/**
 * The class to handle the home page. It extends the SectionPage and will render
 * all sections that are attributed to the home page in the DB.
 */
class ExportPage extends BasePage
{
    /* Private Properties *****************************************************/

    /**
     * The CSV seperator.
     */
    private $separator = ',';

    /**
     * The constructor of this class. It calls the constructor of the parent
     * class and checks the login fields if they are set. If the fields are set
     * but the login fails, the page is redirected to the login page.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        parent::__construct($services, "export");
        $this->separator = $this->get_separator();
    }

    /* Private Methods ********************************************************/

    /**
     * Checks whether the current user is allowed to export validation codes.
     *
     * @retval bool
     *  True if the current user can export validation codes, false otherwise.
     */
    private function can_export_codes()
    {
        return $this->services->get_acl()->has_access_select($_SESSION['id_user'],
            $this->services->get_db()->fetch_page_id_by_keyword("userSelect"));
    }

    /**
     * Fetch the CSV seperator from the database.
     *
     * @retval string
     *  The CSV seperator.
     */
    private function get_separator()
    {
        $sql = "SELECT csv_separator FROM languages WHERE locale = :locale";
        $res = $this->services->get_db()->query_db_first($sql, array(
            ':locale' => $_SESSION['language']
        ));
        if($res)
            return $res['csv_separator'];
        return ',';
    }

    /**
     * Prepare the haders and an output stream such that a CSV file can be made
     * available for download by the browser.
     *
     * @param string $selector
     *  An identifier indicating which data to export.
     * @param string $option
     *  An option to add specifics of what to export
     * @param string $id
     *  The id of a specific input form to export
     */
    private function export_data($selector, $option, $id)
    {
         // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');  
        if($selector !== "user_input_form"){
            // get and post difference - we cannot set header later in get request
            header('Content-Disposition: attachment; filename=' . date("Y-m-d\TH:i:s") . 'Z_' . $selector . '.csv');
        }
        // log user activity on export pages
        $this->services->get_db()->insert("user_activity", array(
            "id_users" => $_SESSION['id_user'],
            "url" => $_SERVER['REQUEST_URI'],
            "id_type" => 2,
        ));        

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        // write data
        $fileName = null;
        if($selector === "user_input")
            $this->export_user_input($output);
        else if($selector === "user_input_form")
            $fileName = $this->export_user_input_form($output, $id); 
        else if($selector === "user_activity")
            $this->export_user_activity($output);
        else if($selector === "validation_codes")
            $this->export_validation_codes($output, $option);
              
        if($selector === "user_input_form"){
            // get and post difference - we cannot set header later in get request
            header('Content-Disposition: attachment; filename=' . date("Y-m-d\TH:i:s") . 'Z_' . $fileName . '.csv');
        }
    }

    /**
     * Writes the user activity in SCV format to the output stream.
     *
     * @param pointer $output
     *  The file pointer to the output stream.
     */
    private function export_user_activity($output)
    {
        $this->fputcsv_wrap($output, array("user_code", "url", "timestamp"));
        $sql = "SELECT ua.url, vc.code, ua.timestamp
            FROM user_activity AS ua
            LEFT JOIN validation_codes AS vc ON vc.id_users = ua.id_users";
        $fields = $this->services->get_db()->query_db($sql);
        foreach($fields as $field)
            $this->fputcsv_wrap($output, array($field['code'], $field['url'],
                $field['timestamp']));
    }

    /**
     * Writes the user inputs in SCV format to the output stream.
     *
     * @param pointer $output
     *  The file pointer to the output stream.
     */
    private function export_user_input($output)
    {

        $fields = $this->services->get_user_input()->get_input_fields();

        // output the column headings
        if(count($fields) > 0)
            $this->fputcsv_wrap($output, array_keys($fields[0]));

        // loop over the rows, outputting them
        foreach($fields as $field)
            $this->fputcsv_wrap($output, $field);
    }

    /**
     * Writes the user inputs in SCV format to the output stream.
     *
     * @param pointer $output
     *  The file pointer to the output stream.
     * @param int $form_id 
     * the form that we want to export
     */
    private function export_user_input_form($output, $form_id)
    {
        $fileName = null;  
        $sql = 'call get_form_data(' . $form_id . ')';
        $fields = $this->services->get_db()->query_db($sql);

        // output the column headings
        if(count($fields) > 0)
            $this->fputcsv_wrap($output, array_keys($fields[0]));

        // loop over the rows, outputting them
        foreach($fields as $field){
            $this->fputcsv_wrap($output, $field);
            if(!$fileName && array_key_exists("form_name", $field)){
               $fileName = $field['form_name'];
            }
        }
        return $fileName;
    }

    /**
     * Writes the user inputs in SCV format to the output stream.
     *
     * @param pointer $output
     *  The file pointer to the output stream.
     * @param string $option
     *  An option to add specifics of what to export
     */
    private function export_validation_codes($output, $option)
    {
        $header = array("codes", "created");
        if($option === "all" || $option === "used")
        {
            $header[] = "user_email";
            $header[] = "consumed";
        }
        $this->fputcsv_wrap($output, $header);
        $sql = "SELECT u.email, vc.code, vc.consumed, vc.created FROM validation_codes AS vc
            LEFT JOIN users AS u ON u.id = vc.id_users
            WHERE 1";
        if($option === "open")
            $sql .= " AND id_users IS NULL";
        else if($option === "used")
            $sql .= " AND id_users IS NOT NULL";
        $fields = $this->services->get_db()->query_db($sql);
        foreach($fields as $field)
        {
            $data = array($field['code'], $field['created']);
            if($option === "all" || $option === "used")
            {
                $data[] = $field['email'];
                $data[] = $field['consumed'];
            }
            $this->fputcsv_wrap($output, $data);
        }
    }

    /**
     * A wrapper for the PHP fputcsv function.
     *
     * @param file $output
     *  The file descriptor to output the csv data to
     * @param array $fields
     *  An array of fields to add to the csv file.
     */
    private function fputcsv_wrap($output, $fields)
    {
        fputcsv($output, $fields, $this->separator);
    }

    /* Protected Methods ******************************************************/

    /**
     * See BasePage::output_content(). This implementation renders all
     * components that are assigned to the current page (as specified in the
     * DB).
     */
    protected function output_content() {}

    /**
     * See BasePage::output_meta_tags()
     * The current implementation is not doing anything.
     */
    protected function output_meta_tags() {}

    /* Public Methods *********************************************************/

    /**
     * Overwrite the base page view renderer. If the access state is in order,
     * present the export file as download. Otherwise render the base page with
     * the standard access denied view.
     *
     * @param string $selector
     *  An identifier indicating which data to export.
     * @param string $option
     *  An option string which allows to specify how to export data.
     * @param string $id
     *  The id of a specific input form to export
     */
    public function output($selector = "", $option = null, $id=null)
    {
        if(!$this->can_export_codes() && $selector === "validation_codes")
        {
            parent::output();
            return;
        }
        if($this->services->get_acl()->has_access($_SESSION['id_user'],
                $this->id_page, $this->required_access_level))
            $this->export_data($selector, $option, $id);
        else
            parent::output();
    }
}
?>
