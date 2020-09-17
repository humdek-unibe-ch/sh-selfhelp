<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../component/BaseModel.php";
/**
 * This class is used to prepare all data related to the asset components such
 * that the data can easily be displayed in the view of the component.
 */
class BMZSportModel extends BaseModel
{

    /* Private Properties *****************************************************/

    /**
     * Survey reposnse array from qualtrics
     */
    private $survey_response;

    /**
     * Response id that comes from qualtrics survey
     */
    private $response_id;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $survey_response
     * An array with the result comming from a qualtrics survey
     * @param string $response_id
     * Response id that comes from qualtrics survey
     */
    public function __construct($services, $survey_response, $response_id)
    {
        parent::__construct($services);
        $this->survey_response = $survey_response;
        $this->response_id = $response_id;
    }

    /* Private Methods ***********************************************************/

    private function evaluate_older_targets()
    {
        $result_array = array();
        $result_array['code'] = isset($this->survey_response['code']) ? $this->survey_response['code'] : $this->response_id;
        $result_array['birth_year'] = $this->survey_response['birth_year'];
        $result_array['age'] = date("Y") - $this->survey_response['birth_year'];
        $result_array['base_allges'] = 112;
        $result_array['base_figaus'] = 95;
        $result_array['base_stimm'] = 105;
        $result_array['base_bewerf'] = 98;
        $result_array['base_wetlei'] = 90;
        $result_array['base_kogn'] = 95;
        $result_array['base_kon'] = 105;
        $data_array = [$this->survey_response['allges'], $this->survey_response['figaus'], $this->survey_response['stimm'], $this->survey_response['bewerf'], $this->survey_response['wetlei'], $this->survey_response['kogn'], $this->survey_response['kon']];
        $mittel_ind7 =  array_sum($data_array) / count($data_array);;
        $sd_ind7 = stats_standard_deviation($data_array);
        $result_array['allges'] = (($this->survey_response['allges'] - $mittel_ind7) / $sd_ind7) * 10 + 100;
        $result_array['figaus'] = (($this->survey_response['figaus'] - $mittel_ind7) / $sd_ind7) * 10 + 100;
        $result_array['stimm'] = (($this->survey_response['stimm'] - $mittel_ind7) / $sd_ind7) * 10 + 100;
        $result_array['bewerf'] = (($this->survey_response['bewerf'] - $mittel_ind7) / $sd_ind7) * 10 + 100;
        $result_array['wetlei'] = (($this->survey_response['wetlei'] - $mittel_ind7) / $sd_ind7) * 10 + 100;
        $result_array['kogn'] = (($this->survey_response['kogn'] - $mittel_ind7) / $sd_ind7) * 10 + 100;
        $result_array['kon'] = (($this->survey_response['kon'] - $mittel_ind7) / $sd_ind7) * 10 + 100;
        return $result_array;
    }

    private function insert_into_db($data)
    {
        $sql = "SELECT id FROM uploadTables WHERE name = :name";
        $name = qualtricsProjectActionAdditionalFunction_bmz_evaluate_motive . '_' . $data['code'];
        $has_table = $this->db->query_db_first($sql, array(":name" => $name));
        if ($has_table) {
            $res = $this->pp_delete_asset_file_static($name);
            if ($res !== true) {
                return $res;
            }
        }

        try {
            $this->db->begin_transaction();
            $id_table = $this->db->insert("uploadTables", array(
                "name" => $name
            ));
            if (!$id_table) {
                $this->db->rollback();
                return "postprocess: failed to create new data table";
            } else {
                if ($this->transaction->add_transaction(transactionTypes_insert, transactionBy_by_qualtrics_callback, null, $this->transaction::TABLE_uploadTables, $id_table) === false) {
                    $this->db->rollback();
                    return false;
                }
                $id_row = $this->db->insert("uploadRows", array(
                    "id_uploadTables" => $id_table
                ));
                if (!$id_row) {
                    $this->db->rollback();
                    return "postprocess: failed to add table rows";
                }
                foreach ($data as $col => $value) {
                    $id_col = $this->db->insert("uploadCols", array(
                        "name" => $col,
                        "id_uploadTables" => $id_table
                    ));
                    if (!$id_col) {
                        $this->db->rollback();
                        return "postprocess: failed to add table cols";
                    }
                    $res = $this->db->insert(
                        "uploadCells",
                        array(
                            "id_uploadRows" => $id_row,
                            "id_uploadCols" => $id_col,
                            "value" => $value
                        )
                    );
                    if (!$res) {
                        $this->db->rollback();
                        return "postprocess: failed to add data values";
                    }
                }
            }
            $this->db->commit();
            return 'Response for code : ' . $data['code'] . ' was successfully inserted in DB';
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Postprocessing DB data after deleting a static data file. The
     * corresponding DB-entries are removed.
     *
     * @param string $name
     *  The name of the file (without extension)
     * @retval mixed
     *  True on success, an error message on failure.
     */
    private function pp_delete_asset_file_static($name)
    {
        $res = $this->db->remove_by_fk("uploadTables", "name", $name);
        if (!$res) {
            return "postprocess: failed to remove old data values";
        }
        return true;
    }

    /* Public Methods *********************************************************/

    public function evaluate_survey()
    {
        $age = date("Y") - $this->survey_response['birth_year'];
        $result = $this->evaluate_older_targets();
        $this->insert_into_db($result);
    }
}
