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
                    if(!$res){
                        $this->db->rollback();
                        return array(
                            "result" => false,
                            "message" => "Error! Column: <code>" . $value . "</code> was not deleted!"
                        );            
                    }
                }
            }
            $this->db->commit();
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
}
