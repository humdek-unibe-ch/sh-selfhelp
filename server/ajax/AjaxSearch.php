<?php

/**
 * A small class to allow to search for users. This class is used for AJAX
 * calls.
 */
class AjaxSearch
{
    /**
     * The instance of the db service.
     */
    private $db = null;

    /**
     * The constructor.
     *
     * @param object $db
     *  The instance of the db service.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * The search function which can be called by an AJAx call.
     *
     * @param $data
     *  The POST data of the ajax call:
     *   - 'search':    the search pattern
     * @retval array
     *  An array of user items where each item has the following keys:
     *   - 'email':     The email of the user.
     *   - 'id':        The id of the user.
     */
    public function search_user_chat($data)
    {
        $sql = "SELECT u.email, u.id FROM users AS u
            LEFT JOIN chatRoom_users AS cru ON cru.id_users = u.id
            WHERE u.email LIKE :search AND u.id > 2
                AND (cru.id_chatRoom IS NULL OR cru.id_chatRoom != :rid)";
        return $this->db->query_db($sql, array(
            ':search' => "%".$data['search']."%",
            ':rid' => $_SESSION['chat_room'],
        ));
    }
}
?>
