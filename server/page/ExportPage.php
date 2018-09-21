<?php
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
     * @param object $router
     *  The router instance is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($router, $db)
    {
        parent::__construct($router, $db, "export");
    }

    /* Private Methods ********************************************************/

    /**
     * Prepare the haders and an output stream such that a CSV file can be made
     * available for download by the browser.
     *
     * @param string $selector
     *  An identifier indicating which data to export.
     */
    private function export_data($selector)
    {
        // log user activity on export pages
        $this->services['db']->insert("user_activity", array(
            "id_users" => $_SESSION['id_user'],
            "url" => $_SERVER['REQUEST_URI'],
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
    }

    /**
     * Writes the user activity in SCV format to the output stream.
     *
     * @param pointer $output
     *  The file pointer to the output stream.
     */
    private function export_user_activity($output)
    {
        fputcsv($output, array("user_hash", "url", "timestamp"));
        $sql = "SELECT id_users AS id, url, timestamp FROM user_activity";
        $fields = $this->services['db']->query_db($sql);
        foreach($fields as $field)
        {
            $hash = substr(base_convert(hash("sha256", $field["id"]), 16, 36), 0, 8);
            fputcsv($output, array($hash, $field['url'], $field['timestamp']));
        }
    }

    /**
     * Writes the user inputs in SCV format to the output stream.
     *
     * @param pointer $output
     *  The file pointer to the output stream.
     */
    private function export_user_input($output)
    {

        $fields = $this->services['user_input']->get_input_fields();

        // output the column headings
        fputcsv($output, array_keys($fields[0]));

        // loop over the rows, outputting them
        foreach($fields as $field)
            fputcsv($output, $field);
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
    public function output($selector = "")
    {
        if($this->services['acl']->has_access($_SESSION['id_user'],
                $this->id_page, $this->required_access_level))
            $this->export_data($selector);
        else
            parent::output();
    }
}
?>
