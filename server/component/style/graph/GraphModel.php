<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";

/**
 * This class is used to prepare all data related to the grap style components
 * such that the data can easily be displayed in the view of the component.
 */
class GraphModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'data-source' (empty string).
     * The data source to be used to draw the graph. This can either be a
     * dynamic or a static data source.
     */
    private $data_source;

    /**
     * DB field 'single-user' (true).
     * If set to true, only use the data set of the currently logged in user.
     * If set to false, use the data set of all users.
     */
    private $single_user;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all session related fields from the database.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of the navigation wrapper.
     */
    public function __construct($services, $id)
    {
        parent::__construct($services, $id);
        $this->data_source = $this->get_db_field("data-source");
        $this->single_user = $this->get_db_field("single_user");
    }

    /* Private Methods ********************************************************/

    /**
     * Read dynamic data form the database. This data is collected dynamically
     * through online forms from subjects.
     *
     * @param number $form_id
     *  The id of the form to fetch.
     * @retval array
     *  Returns a list of assiciative arrays items. Each item corresponds to a
     *  data set collected from one form submission. The keys of each item
     *  correspond to the field names of the form.
     */
    private function read_data_source_dynamic($form_id)
    {
        if($this->single_user) {
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
    private function read_data_source_static($table_id)
    {
        $sql = 'CALL get_uploadTable(' . $table_id . ')';
        return $this->db->query_db($sql);
    }

    /* Protected Methods ******************************************************/

    /**
     * Read the source data from the database. This can either be static or
     * dynamic data depending on what was selected.
     *
     * @retval array
     *  A list of data items fetched from the DB. Refer to
     *  GraphModel::read_data_source_static() and
     *  GraphModel::read_data_source_dynmaic() for more information.
     *  If an error occurred, false is returned.
     */
    protected function read_data_source()
    {
        $sql = "SELECT * FROM view_data_tables WHERE table_name = :name";
        $source = $this->db->query_db_first($sql,
            array("name" => $this->data_source));
        if($source['type'] === "static") {
            return $this->read_data_source_static($source['id']);
        } else if($source['type'] === "dynamic") {
            return $this->read_data_source_dynamic($source['id']);
        }
        return false;
    }

    /* Public Methods *********************************************************/

    public function get_data_source()
    {
        return $this->data_source;
    }

    public function get_single_user()
    {
        return $this->single_user;
    }

    /**
     * Checks wether the types array provided through the CMS contains all
     * required fields.
     *
     * @param array $value_types
     *  The array to be checked.
     * @retval boolean
     *  True on success, false on failure.
     */
    public function check_value_types($value_types) {
        if(!is_array($value_types) || count($value_types) === 0)
            return false;
        foreach($value_types as $idx => $item)
        {
            if(!isset($item["key"]))
                return false;
            if(!isset($item["label"]))
                return false;
        }
        return true;
    }

    public function extract_labels($value_types) {
        $labels = array();
        foreach($value_types as $type) {
            $labels[$type['key']] = $type['label'];
        }
        return $labels;
    }

    public function extract_colors($value_types) {
        $colors = array();
        foreach($value_types as $type) {
            if(isset($type['color']))
                $colors[$type['key']] = $type['color'];
        }
        return $colors;
    }
}
?>
