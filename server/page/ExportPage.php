<?php
require_once __DIR__ . "/BasePage.php";
require_once __DIR__ . "/../service/UserInput.php";

/**
 * The class to handle the home page. It extends the SectionPage and will render
 * all sections that are attributed to the home page in the DB.
 */
class ExportPage extends BasePage
{
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
    }

    /* Private Methods ********************************************************/

    /**
     * Prepare the haders and an output stream such that a CSV file can be made
     * available for download by the browser.
     *
     * @param string $selector
     *  An identifier indicating which data to export.
     * @param string $option
     *  An option to add specifics of what to export
     */
    private function export_data($selector, $option)
    {
        // log user activity on export pages
        $this->services->get_db()->insert("user_activity", array(
            "id_users" => $_SESSION['id_user'],
            "url" => $_SERVER['REQUEST_URI'],
            "id_type" => 2,
        ));

        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$selector.'.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        // write data
        if($selector === "user_input")
            $this->export_user_input($output);
        else if($selector === "user_activity")
            $this->export_user_activity($output);
        else if($selector === "validation_codes")
            $this->export_validation_codes($output, $option);
    }

    /**
     * Writes the user activity in SCV format to the output stream.
     *
     * @param pointer $output
     *  The file pointer to the output stream.
     */
    private function export_user_activity($output)
    {
        fputcsv($output, array("user_code", "url", "timestamp"));
        $sql = "SELECT ua.url, vc.code, ua.timestamp
            FROM user_activity AS ua
            LEFT JOIN validation_codes AS vc ON vc.id_users = ua.id_users";
        $fields = $this->services->get_db()->query_db($sql);
        foreach($fields as $field)
            fputcsv($output, array($field['code'], $field['url'],
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
            fputcsv($output, array_keys($fields[0]));

        // loop over the rows, outputting them
        foreach($fields as $field)
            fputcsv($output, $field);
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
        $header = array("codes");
        if($option === "all" || $option === "used")
        {
            $header[] = "user_email";
            $header[] = "timestamp";
        }
        fputcsv($output, $header);
        $sql = "SELECT u.email, vc.code, vc.timestamp FROM validation_codes AS vc
            LEFT JOIN users AS u ON u.id = vc.id_users
            WHERE 1";
        if($option === "open")
            $sql .= " AND id_users IS NULL";
        else if($option === "used")
            $sql .= " AND id_users IS NOT NULL";
        $fields = $this->services->get_db()->query_db($sql);
        foreach($fields as $field)
        {
            $data = array($field['code']);
            if($option === "all" || $option === "used")
            {
                $data[] = $field['email'];
                $data[] = $field['timestamp'];
            }
            fputcsv($output, array($field['code']));
        }
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
     */
    public function output($selector = "", $option = null)
    {
        if($this->services->get_acl()->has_access($_SESSION['id_user'],
                $this->id_page, $this->required_access_level))
            $this->export_data($selector, $option);
        else
            parent::output();
    }
}
?>
