<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";

/**
 * This class is used to prepare all data related to the emailFormBase style
 * components such that the data can easily be displayed in the view of the
 * component.
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
     *
     */
    private function read_data_source_dynamic($table_id)
    {
        $sql = 'CALL get_uploadTable(' . $table_id . ')';
        return $this->db->query_db($sql);
    }

    /**
     *
     */
    private function read_data_source_static($form_id)
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

    /* Protected Methods ******************************************************/

    /**
     * Read the source data from the database. This can either be static or
     * dynamic data depending on what was selected.
     *
     * @retval array
     *  An associative array with the following keys:
     *   - `head`: An array of strings describing the csv head.
     *   - `body`: An array of rows where each row is an array of values.
     */
    protected function read_data_source()
    {
        $sql = "SELECT * FROM view_data_tables WHERE name = ':name'";
        $source = $this->db->query_db_first($sql,
            array("name" => $this->data_source));
        if($source['type'] === "static") {
            $data = $this->read_data_source_static($source['id']);
        } else if($source['type'] === "dynamic") {
            $data = $this->read_data_source_dynamic($source['id']);
        }
        return array(
            "head" => $head,
            "body" => $body
        );
    }

    /* Public Methods *********************************************************/
}
?>
