<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the cmsPreference component such
 * that the data can easily be displayed in the view of the component.
 */
class DataDeleteModel extends BaseModel
{
    /* Private Properties *****************************************************/

    /**
     * The id of the selected dataTable
     */
    private $id_dataTables;

    /**
     * The dataTable structure
     */
    private $dataTable;

    /**
     * The constructor
     *
     * @param object $services
     *  An associative array holding the different available services. See the
     *  class definition base page for a list of all services.
     * @param int $id
     *  The id of the section with the conditional container style.
     * @param array $params
     *  The list of get parameters to propagate.     
     */
    public function __construct($services, $id, $params, $id_dataTables)
    {
        parent::__construct($services, $id, $params);
        $this->id_dataTables = $id_dataTables;
        $this->dataTable = $this->fetch_dataTable();
    }

    /* Private Methods *********************************************************/

    /**
     * Fetch the dataTable structure
     * @return array
     * Return the dataTable structure
     */
    private function fetch_dataTable()
    {
        $sql = 'SELECT DISTINCT d.table_id, d.row_id, d.col_id, t.`name`, t.name_id, d.col_name
                FROM view_dataTables_data d
                INNER JOIN view_dataTables t ON (d.table_id = t.id)
                WHERE table_id = :id_dataTables;';
        return $this->db->query_db($sql, array(
            ":id_dataTables" => $this->id_dataTables
        ));
    }


    /* Public Methods *********************************************************/

    /**
     * Getter for the dataТable
     * @return array dataTable
     * The dataTable structure
     */
    public function get_dataTable()
    {
        return $this->dataTable;
    }

    /**
     * Get data columns for the selected dataTable
     * @return array
     * All the columns for the dataTable
     */
    public function fetch_dataColumns()
    {
        $sql = 'SELECT *
                FROM dataCols
                WHERE id_dataTables =  :id_dataTables;';
        return $this->db->query_db($sql, array(
            ":id_dataTables" => $this->id_dataTables
        ));
    }

    /**
     * Deletes columns from the database.
     *
     * This function attempts to delete columns from the `dataCols` table based on the provided column IDs.
     * It uses a transaction to ensure that all columns are deleted successfully. If any deletion fails,
     * the transaction is rolled back and an error message is returned.
     *
     * @param array $columns An associative array where the key is the column ID and the value is the column name.
     *                       If the value is true, the column is marked for deletion.
     * 
     * @return array An associative array containing the result of the operation:
     *               - "result": A boolean indicating whether the operation was successful.
     *               - "message": A string containing a success or error message.
     * 
     * @throws Exception If an error occurs during the deletion process, an exception is caught, 
     *                   the transaction is rolled back, and an error message is returned.
     */
    public function delete_columns($columns)
    {
        $res = true;
        $column_names = array();
        try {
            $this->db->begin_transaction();
            foreach ($columns as $key => $value) {
                if ($value) {
                    $res = $res && $this->db->remove_by_ids("dataCols", array(
                        "id" => $key,
                        "id_dataTables" => $this->id_dataTables
                    ));
                    $column_names[] = $value;
                    if (!$res) {
                        $this->db->rollback();
                        return array(
                            "result" => false,
                            "message" => "Error! Column: <code>" . $value . "</code> was not deleted!"
                        );
                    }
                }
            }
            $this->db->commit();
            $this->db->clear_cache($this->db->get_cache()::CACHE_TYPE_SECTIONS);
            $this->db->clear_cache($this->db->get_cache()::CACHE_TYPE_USER_INPUT);
            return array(
                "result" => true,
                "message" => "Columns: `" . implode(', ', $column_names) . "` were successfully deleted!"
            );
        } catch (Exception $e) {
            $this->db->rollback();
            return array(
                "result" => false,
                "message" => "Error while deleting columns!"
            );
        }
    }

    /**
     * Deletes a data table from the database.
     *
     * This function removes a data table from the `dataTables` table based on the current object's `id_dataTables`.
     * It also clears specific cache types related to sections and user input.
     *
     * @return array An associative array containing the result of the operation:
     *               - "result": A boolean indicating whether the operation was successful.
     *               - "message": A string containing a success or error message.
     */
    public function delete_dataTable()
    {
        $res = $this->db->remove_by_ids("dataTables", array(
            "id" => $this->id_dataTables
        ));
        $this->db->clear_cache($this->db->get_cache()::CACHE_TYPE_SECTIONS);
        $this->db->clear_cache($this->db->get_cache()::CACHE_TYPE_USER_INPUT);
        return array(
            "result" => ($res ? true : false),
            "message" => ($res ? "Data table `" . $this->dataTable[0]['name'] . ' ` was successfully deleted!' : "Error! Data table `" . $this->dataTable[0]['name'] . ' ` was not deleted!')
        );
    }
}
