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
     * The search function which can be called by an AJAx call.
     *
     * @param $data
     *  The POST data of the ajax call:
     *   - 'search':    the search pattern
     * @retval array
     *  An array of user items where each item has the following keys:
     *   - 'value':     The email of the user.
     *   - 'id':        The id of the user.
     */
    public function search_user_chat($data)
    {
        $sql = "SELECT u.email AS value, u.id AS id FROM users AS u
            LEFT JOIN chatRoom_users AS cru ON cru.id_users = u.id
            WHERE u.email LIKE :search AND u.id > 2
                AND (cru.id_chatRoom IS NULL OR cru.id_chatRoom != :rid)";
        return $this->db->query_db($sql, array(
            ':search' => "%".$data['search']."%",
            ':rid' => $_SESSION['chat_room'],
        ));
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
            WHERE table_name LIKE :search ORDER BY table_name";
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
                    OR s.id_styles = 3 OR s.id_styles = 39)
                AND s.name LIKE :search ORDER BY value";
        return $this->db->query_db($sql, array(
            'search' => "%".$data['search']."%"
        ));
    }
}
?>
