<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseAjax.php";

/**
 * A small class to allow to fetch static or dynamic datat from the DB. This
 * class is used for AJAX calls.
 */
class AjaxDataSource extends BaseAjax
{
    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services)
    {
        parent::__construct($services);
    }

    /* Private Methods ********************************************************/

    /**
     * Read dynamic data form the database. This data is collected dynamically
     * through online forms from subjects.
     *
     * @param number $form_id
     *  The id of the form to fetch.
     * @param boolean $single_user
     *  If true only fetch dynamic data from a single user, if false fetch
     *  dynamic data from all users.
     * @retval array
     *  Returns a list of assiciative arrays items. Each item corresponds to a
     *  data set collected from one form submission. The keys of each item
     *  correspond to the field names of the form.
     */
    private function fetch_data_table_dynamic($form_id, $single_user)
    {
        if($single_user) {
            $sql = 'CALL get_form_data_for_user(' . $form_id . ', '
                . $_SESSION['id_user'] . ')';
            return $this->db->query_db($sql);
        } else {
            $sql = 'CALL get_form_data(' . $form_id . ')';
            return $this->db->query_db($sql);
        }
    }

    /**
     * Read static data from the database. This data is collected through a CSV
     * file upload.
     *
     * @param number $table_id
     *  The id of the uploaded CSV table.
     * @retval array
     *  Returns a list of assiciative arrays items. Each item corresponds to
     *  a row of the data table. The keys of each item correspond to the column
     *  names of the table.
     */
    private function fetch_data_table_static($table_id)
    {
        $sql = 'CALL get_uploadTable(:id)';
        return $this->db->query_db($sql, array(
            "id" => $table_id
        ));
    }

    /* Public Methods *********************************************************/

    /**
     * The search function which can be called by an AJAx call.
     *
     * @param $data
     *  The POST data of the ajax call:
     *   - 'name':        the name of the data to fetch.
     *   - 'single_user': flag to indicate whether to use dynamic data of a
     *                    single user or of all users
     * @retval array
     *  An array of user items where each item has the following keys:
     *   - 'value':     The email of the user.
     *   - 'id':        The id of the user.
     */
    public function get_data_table($data)
    {
        $sql = "SELECT * FROM view_data_tables WHERE table_name = :name";
        $source = $this->db->query_db_first($sql,
            array("name" => $data['name']));
        if($source['type'] === "static") {
            return $this->fetch_data_table_static($source['id']);
        } else if($source['type'] === "dynamic") {
            return $this->fetch_data_table_dynamic($source['id'], $data['single_user']);
        }
        return false;
    }
}
?>
