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
     *   - 'value':     The email of the user.
     *   - 'id':        The id of the user.
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
}
?>
