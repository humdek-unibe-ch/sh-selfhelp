<?php
require_once __DIR__ . "/../service/UserInput.php";

/**
 * The class to handle the home page. It extends the SectionPage and will render
 * all sections that are attributed to the home page in the DB.
 */
class ExportPage
{
    private $user_input;

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
    public function __construct($db)
    {
        $this->user_input = new UserInput($db);
    }

    public function export_data()
    {
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        $fields = $this->user_input->get_input_fields();
        // output the column headings
        fputcsv($output, array_keys($fields[0]));

        // loop over the rows, outputting them
        foreach($fields as $field)
            fputcsv($output, $field);
    }
}
?>
