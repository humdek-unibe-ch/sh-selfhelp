<?php

class AjaxSearch
{
    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

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
