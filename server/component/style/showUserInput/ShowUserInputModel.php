<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     * @param array $params
     *  The list of get parameters to propagate.
     * @param number $id_page
     *  The id of the parent page
     * @param array $entry_record
     *  An array that contains the entry record information.
     */
    public function __construct($services, $id, $params, $id_page, $entry_record)
    {
        parent::__construct($services, $id, $params, $id_page, $entry_record);
    }

    /* Public Methods *********************************************************/

    /**
     * Get the input data of the current user from the database of a given data table.
     *
     * @param string $data_table
     *  The name of the data table from which the data will be fetched.
     * @return array     
     */
    public function get_user_data($data_table)
    {
        $res =  $this->user_input->get_data($data_table, '');
        $fields_map = $this->get_db_field('fields_map');
        if (count($fields_map) > 0) {
            // map the fields with the new label
            $mappedRows = [];
            foreach ($res as $row) {
                $mappedRow = [];
                $mappedRow[ENTRY_RECORD_ID] = $row[ENTRY_RECORD_ID];
                foreach ($fields_map as $field => $label) {
                    if (isset($row[$field])) {
                        $mappedRow[$label] = $row[$field];
                    }
                }
                $mappedRows[] = $mappedRow;
            }
            return $mappedRows;
        } else {
            // there is no mapping
            return $res;
        }
    }

    /**
     * Mark this user input as deleted in the database.
     * @param int $record_id
     * The deleted record id
     */
    public function delete_record($record_id)
    {
        return $this->user_input->delete_data($record_id);
    }

    /**
     * Prepare the form data and call the queue_job_from_actions
     * @param string $trigger_type
     * Form started or finished
     */
    public function queue_job_from_actions($trigger_type, $record_id = null)
    {
        $filter = "AND record_id = " . $record_id;
        $form_id = $this->user_input->get_dataTable_id($this->get_db_field("source"));
        $form_fields = $this->user_input->get_data($form_id, $filter, true, null, true);
        $form_data = array(
            "trigger_type" => $trigger_type,
            "form_name" => $this->get_db_field("name"),
            "form_id" => $this->section_id,
            "form_fields" => $form_fields
        );
        $this->user_input->queue_job_from_actions($form_data);
    }

    /**
     * Wrapper function to convert a string to alphanumeric values.
     *
     * @param string $str
     *  The string to convert
     * @retval
     *  The converted string
     */
    public function convert_to_alphanumeric($str)
    {
        return $this->user_input->convert_to_valid_html_id($str);
    }
}
?>
