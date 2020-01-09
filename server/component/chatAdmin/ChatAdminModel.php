<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseModel.php";
/**
 * This class is used to prepare all data related to the chatAdmin component
 * such that the data can easily be displayed in the view of the component.
 */
class ChatAdminModel extends BaseModel
{
    /* Private Properties *****************************************************/

    /**
     * The id of the currently selected chat room.
     */
    private $rid;

    /**
     * The name of the currently selected chat room.
     */
    private $room_name = "";

    /**
     * The description of the currently selected chat room.
     */
    private $room_desc = "";

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $rid
     *  The id of the current selected chat room.
     */
    public function __construct($services, $rid=null)
    {
        parent::__construct($services);
        $this->rid = $rid;
        $this->fetch_active_room_info();
        $_SESSION['chat_room'] = $rid;
    }

    /* Private Methods ********************************************************/

    /**
     * Fetch the active room info from the db and store it as class property.
     */
    private function fetch_active_room_info()
    {
        $sql = "SELECT name, description FROM chatRoom WHERE id = :id";
        $res = $this->db->query_db_first($sql, array(':id' => $this->rid));
        if($res)
        {
            $this->room_name = $res['name'];
            $this->room_desc = $res['description'];
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Add users to the active chat room.
     *
     * @param int $user
     *  The id of a user to be added the the active chat room.
     * @retval bool
     *  True on success, false on failure.
     */
    public function add_user_to_active_room($user)
    {
        return $this->db->insert("chatRoom_users", array(
            "id_users" => $user,
            "id_chatRoom" => $this->rid,
        ));
    }

    /**
     * Checks whether the current user is allowed to create new chat rooms.
     *
     * @retval bool
     *  True if the current user can create new chat rooms, false otherwise.
     */
    public function can_create_new_room()
    {
        return $this->acl->has_access_insert($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("chatAdminInsert"));
    }

    /**
     * Checks whether the current user is allowed to delete chat rooms.
     *
     * @retval bool
     *  True if the current user can delete chat rooms, false otherwise.
     */
    public function can_delete_room()
    {
        return $this->acl->has_access_delete($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("chatAdminDelete"));
    }

    /**
     * Checks whether the current user is allowed to add and remove users from
     * chat rooms.
     *
     * @retval bool
     *  True if the current user can administrate the chat, false otherwise.
     */
    public function can_administrate_chat()
    {
        return $this->acl->has_access_update($_SESSION['id_user'],
            $this->db->fetch_page_id_by_keyword("chatAdminUpdate"));
    }

    /**
     * Create a new chat room.
     *
     * @param string $name
     *  The name of the chat room.
     * @param string $desc
     *  The description of the new chat room.
     * @retval int
     *  The id of the new chat room.
     */
    public function create_new_room($name, $desc)
    {
        return $this->db->insert('chatRoom', array('name' => $name,
            'description' => $desc));
    }

    /**
     * Delete the active chat room.
     *
     * @return bool
     *  True on success, false on failure.
     */
    public function delete_active_room()
    {
        return $this->db->remove_by_ids('chatRoom', array('id' => $this->rid));
    }

    /**
     * Get the currently active room.
     *
     * @retval int
     *  The id of the currently active room.
     */
    public function get_active_room()
    {
        return $this->rid;
    }

    /**
     * Return the active room description.
     *
     * @retval string
     *  The description of the active room.
     */
    public function get_active_room_desc()
    {
        return $this->room_desc;
    }

    /**
     * Return the active room name.
     *
     * @retval string
     *  The name of the active room.
     */
    public function get_active_room_name()
    {
        return $this->room_name;
    }

    /**
     * Fetch all users from the active room.
     *
     * @retval array
     *  A list of db items with the keys:
     *   - 'id':    The id of the user.
     *   - 'title': The email address of the user.
     */
    public function get_active_room_users()
    {
        $sql = "SELECT u.id, u.email AS title, GROUP_CONCAT(ug.id_groups SEPARATOR ', ') AS group_ids, 
        CASE 
            WHEN (SELECT COUNT(*) FROM users_groups mods WHERE mods.id_users = u.id AND mods.id_groups = :gid) > 0 THEN 1
            ELSE 0
        END AS is_mod
        FROM users AS u
        LEFT JOIN chatRoom_users AS cru ON cru.id_users = u.id
        LEFT JOIN users_groups AS ug ON ug.id_users = u.id
        WHERE cru.id_chatRoom = :rid
        GROUP BY u.id, u.email
        ORDER BY group_ids, u.email";        
        return $this->db->query_db($sql, array(
            ':rid' => $this->rid,
            ':gid' => EXPERIMENTER_GROUP_ID,
        ));
    }

    /**
     * Get a list of existing rooms from the database (except the root room)
     *
     * @retval array
     *  An array of items where each item has the following keys:
     *   - 'id':    The id of the room
     *   - 'name':  The name of the room
     *   - 'url':   The url to the room
     */
    public function get_rooms()
    {
        $rooms = array();
        $sql = "SELECT id, name from chatRoom WHERE id != 1";
        $rooms_db = $this->db->query_db($sql);
        foreach($rooms_db as $room)
            $rooms[] = array(
                'id' => intval($room['id']),
                'title' => $room['name'],
                'url' => $this->get_link_url('chatAdminSelect', array(
                    'rid' => intval($room['id'])
                )),
            );
        return $rooms;
    }

    /**
     * Fetch the email address of a user from the db, given an id
     *
     * @param int $id
     *  The user id
     * @retval string
     *  The email address of the user.
     */
    public function get_user_email($id)
    {
        $sql = "SELECT email FROM users WHERE id = :id";
        $res = $this->db->query_db_first($sql, array(':id' => $id));
        if($res)
            return $res['email'];
        return "";
    }

    /**
     * Remove a user from the active chat room.
     *
     * @param int $uid
     *  The id of the user to be removed from the active chat room.
     * @retval bool
     *  True on success, false on failure.
     */
    public function rm_user_from_active_room($uid)
    {
        return $this->db->remove_by_ids("chatRoom_users", array(
            "id_users" => $uid,
            "id_chatRoom" => $this->rid,
        ));
    }
}
?>
