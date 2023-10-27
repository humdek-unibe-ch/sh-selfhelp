<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseAjax.php";

/**
 * A small class to allow to search for users. This class is used for AJAX
 * calls.
 */
class AjaxSearch extends BaseAjax
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

    /**
     * Search for data sources to be used to display tables or graphs.
     *
     * @param $data
     *  The POST data of the ajax call:
     *   - 'search':    the search pattern
     * @retval array
     *  An array of user items where each item has the following keys:
     *   - 'value':     The table name.
     *   - 'id':        The table name.
     */
    public function search_data_source($data)
    {
        $sql = "SELECT table_name AS value, table_name AS id
            FROM view_data_tables
            WHERE internal <> 1 AND table_name LIKE :search ORDER BY table_name";
        return $this->db->query_db($sql, array(
            'search' => "%".$data['search']."%"
        ));
    }

    /**
     * Search for section names with a anchor-style given a search pattern.
     *
     * @param $data
     *  The POST data of the ajax call:
     *   - 'search':    the search pattern
     * @retval array
     *  An array of user items where each item has the following keys:
     *   - 'value':     The name of the section.
     *   - 'id':        The id of the section.
     */
    public function search_anchor_section($data)
    {
        $sql = "SELECT s.name AS value, CAST(s.id AS unsigned) AS id FROM sections AS s
            LEFT JOIN styles AS st ON s.id_styles = st.id
            WHERE (s.id_styles = 14 OR s.id_styles = 12 OR s.id_styles = 11
                    OR s.id_styles = 3 OR s.id_styles = 39 OR s.id_styles = 24)
                AND s.name LIKE :search ORDER BY value";
        return $this->db->query_db($sql, array(
            'search' => "%".$data['search']."%"
        ));
    }
}
?>
