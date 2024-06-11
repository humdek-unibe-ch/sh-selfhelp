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
        return $this->user_input->get_data($data_table, '');        
    }

    /**
     * Mark this user input as removed in the database.
     *
     * @param array $ids
     *  The id of the field to be marked as removed.
     * @param int $record_id
     * The deleted record id
     */
    public function mark_user_input_as_removed($ids, $record_id)
    {
        try {
            $this->db->begin_transaction();
            foreach ($ids as $id) {
                if ($id != ENTRY_RECORD_ID) {
                    $this->db->update_by_ids(
                        'user_input',
                        array('removed' => 1),
                        array('id' => $id)
                    );
                }
            }
            $this->queue_job_from_actions(actionTriggerTypes_deleted, $record_id);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
        }
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
