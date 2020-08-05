<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
/**
 * This class handles the Access Control Layer (ACL).
 */
class Acl
{
    /* Private Properties *****************************************************/

    /**
     * The db instance which grants access to the DB.
     */
    private $db;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $db
     *  The db instance which grants access to the DB.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /* Private Methods ********************************************************/

    /**
     * Connects to the database and gets the access rights of a user or a group
     * for a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval array
     *  An associative array with the acces rights: 'select', 'insert',
     *  'update', and 'delete'.
     */
    private function get_access_levels($id, $id_page, $is_group = false)
    {
        if($is_group)
            return $this->get_access_levels_group($id, $id_page);
        else
            return $this->get_access_levels_user($id, $id_page);
    }

    /**
     * Connects to the database and gets the acces rights of a group for a
     * specific page.
     *
     * @param int $id_group
     *  The unique identifier of the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval array
     *  The result from the db query or false on failure.
     */
    private function get_access_levels_db_group($id_group, $id_page)
    {
        $sql = "SELECT * FROM view_acl_groups_pages_modules            
            WHERE `enabled` = 1 AND id_groups = :gid AND id_pages = :pid";
        $arguments = array(
            ":gid" => $id_group,
            ":pid" => $id_page
        );
        return $this->db->query_db_first($sql, $arguments);
    }

    /**
     * Connects to the database and gets the acces rights of a user for a
     * specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval array
     *  The result from the db query or false on failure.
     */
    private function get_access_levels_db_user($id_user, $id_page)
    {
        $sql = "SELECT * FROM view_acl_users_pages_modules            
            WHERE `enabled` = 1 AND id_users = :uid AND id_pages = :pid";
        $arguments = array(
            ":uid" => $id_user,
            ":pid" => $id_page
        );
        return $this->db->query_db_first($sql, $arguments);
    }

    /**
     * Connects to the database and gets the access rights of a user inherited 
     * from the groups the user belongs to for a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval array
     *  The result from the db query or false on failure.
     */
    private function get_access_levels_db_user_groups($id_user, $id_page)
    {
        $sql = "SELECT ag.acl_select, ag.acl_insert, ag.acl_update, ag.acl_delete
            FROM view_acl_groups_pages_modules AS ag         
            LEFT JOIN users_groups AS ug ON ag.id_groups = ug.id_groups 
            WHERE ag.enabled = 1 AND ug.id_users = :uid AND ag.id_pages = :pid";
        $arguments = array(
            ":uid" => $id_user,
            ":pid" => $id_page
        );
        return $this->db->query_db($sql, $arguments);
    }

    /**
     * Connects to the database and gets the access rights of a group for a
     * specific page.
     *
     * @param int $id_group
     *  The unique identifier of the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval array
     *  An associative array with the acces rights: 'select', 'insert',
     *  'update', and 'delete'.
     */
    private function get_access_levels_group($id_group, $id_page)
    {
        $acl = array(
            "select" => false,
            "insert" => false,
            "update" => false,
            "delete" =>false
        );
        $acl_db_group = $this->get_access_levels_db_group($id_group, $id_page);
        if($acl_db_group)
        {
            if($acl_db_group['acl_select'] == '1') $acl["select"] = true;
            if($acl_db_group['acl_insert'] == '1') $acl["insert"] = true;
            if($acl_db_group['acl_update'] == '1') $acl["update"] = true;
            if($acl_db_group['acl_delete'] == '1') $acl["delete"] = true;
        }
        return $acl;
    }

    /**
     * Connects to the database and gets the access rights of a user for a
     * specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval array
     *  An associative array with the acces rights: 'select', 'insert',
     *  'update', and 'delete'.
     */
    private function get_access_levels_user($id_user, $id_page)
    {
        // $starttime = microtime(true);
        $acl = array(
            "select" => false,
            "insert" => false,
            "update" => false,
            "delete" =>false
        );
        $acl_db_user = $this->get_access_levels_db_user($id_user, $id_page);
        if($acl_db_user)
        {
            if($acl_db_user['acl_select'] == '1') $acl["select"] = true;
            if($acl_db_user['acl_insert'] == '1') $acl["insert"] = true;
            if($acl_db_user['acl_update'] == '1') $acl["update"] = true;
            if($acl_db_user['acl_delete'] == '1') $acl["delete"] = true;
        }
        $acl_db_groups = $this->get_access_levels_db_user_groups($id_user,
            $id_page);
        foreach($acl_db_groups as $acl_db_group)
            if($acl_db_group)
            {
                if($acl_db_group['acl_select'] == '1') $acl["select"] = true;
                if($acl_db_group['acl_insert'] == '1') $acl["insert"] = true;
                if($acl_db_group['acl_update'] == '1') $acl["update"] = true;
                if($acl_db_group['acl_delete'] == '1') $acl["delete"] = true;
            }
        // $acl = array(
        //     "select" => true,
        //     "insert" => true,
        //     "update" => true,
        //     "delete" =>true
        // ); //using for debuging when I will fix the group loading page
        // $endtime = microtime(true);
        //print("duration: " .  ($endtime - $starttime) . "<br>");
        return $acl;
    }

    /**
     * Connects to the database and sets the acces rights of a user for a
     * specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param array $acl
     *  An associative array with the acces rights to set where the keys are:
     *  'select', 'insert', 'update', and 'delete'.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    private function set_access_levels($id, $id_page, $acl, $is_group = false)
    {
        $mode = ($is_group) ? "groups" : "users";
        $acl_db = array();
        foreach($acl as $key => $value)
            $acl_db["acl_" . $key] = ($value) ? '1' : '0';

        $ids = array(
            "id_" . $mode => $id,
            "id_pages" => $id_page
        );

        if($this->db->insert("acl_" . $mode, array_merge($acl_db, $ids),
                $acl_db))
            return true;
        return false;
    }

    /* Public Methods *********************************************************/

    /**
     * Checks whether page access rights of a user are higher or equal to the
     * page access rights of a group.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param int $id_group
     *  The unique identifier of the group.
     * @retval bool
     *  Returns true if the user has at least the same access level as the
     *  group for each page. Otherwise false is returned.
     *
     */
    public function is_user_of_higer_level_than_group($id_user, $id_group)
    {        
        $sql = "SELECT id FROM pages";
        $pages_db = $this->db->query_db($sql);
        foreach($pages_db as $page)
        {
            $id_page = intval($page['id']);
            $acl_user = $this->get_access_levels_user($id_user, $id_page);
            $acl_group = $this->get_access_levels_group($id_group, $id_page);
            if(!$acl_user['delete'] && $acl_group['delete'])
                return false;
            if(!$acl_user['update'] && $acl_group['update'])
                return false;
            if(!$acl_user['insert'] && $acl_group['insert'])
                return false;
            if(!$acl_user['select'] && $acl_group['select'])
                return false;
        }
        return true;
    }

    /**
     * Checks whether page access rights of a user are higher or equal to the
     * page access rights of another user.
     *
     * @param int $id_user_1
     *  The unique identifier of one user.
     * @param int $id_user_2
     *  The unique identifier of another user.
     * @retval bool
     *  Returns true if the user has at least the same access level as the
     *  other user for each page. Otherwise false is returned.
     *
     */
    public function is_user_of_higer_level_than_user($id_user_1, $id_user_2)
    {
        $sql = "SELECT id FROM pages";
        $pages_db = $this->db->query_db($sql);
        foreach($pages_db as $page)
        {
            $id_page = intval($page['id']);
            $acl_user_1 = $this->get_access_levels_user($id_user_1, $id_page);
            $acl_user_2 = $this->get_access_levels_user($id_user_2, $id_page);
            if(!$acl_user_1['delete'] && $acl_user_2['delete'])
                return false;
            if(!$acl_user_1['update'] && $acl_user_2['update'])
                return false;
            if(!$acl_user_1['insert'] && $acl_user_2['insert'])
                return false;
            if(!$acl_user_1['select'] && $acl_user_2['select'])
                return false;
        }
        return true;
    }

    /**
     * Grants the user delete access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function grant_access_delete($id, $id_page, $is_group = false)
    {
        return $this->set_access_levels($id, $id_page,
            array("delete" => true), $is_group);
    }

    /**
     * Grants the user insert access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function grant_access_insert($id, $id_page, $is_group = false)
    {
        return $this->set_access_levels($id, $id_page,
            array("insert" => true), $is_group);
    }

    /**
     * Grants the user access to a specific page for all access levels up to a
     * specified level.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param int $level
     *  The access level. The access levels are ordered as follows:
     *  1. select
     *  2. insert
     *  3. update
     *  4. delete
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function grant_access_levels($id, $id_page, $level,
        $is_group = false)
    {
        if($level > 4) $level = 4;
        $acl = array();
        switch($level)
        {
            case 4: $acl['delete'] = true;
            case 3: $acl['update'] = true;
            case 2: $acl['insert'] = true;
            case 1:
                $acl['select'] = true;
                break;
            default: return true;;
        }
        return $this->set_access_levels($id, $id_page, $acl, $is_group);
    }

    /**
     * Grants the user select access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function grant_access_select($id, $id_page, $is_group = false)
    {
        return $this->set_access_levels($id, $id_page,
            array("select" => true), $is_group);
    }

    /**
     * Grants the user update access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function grant_access_update($id, $id_page, $is_group = false)
    {
        return $this->set_access_levels($id, $id_page,
            array("update" => true), $is_group);
    }

    /**
     * Verifies user delete access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param string $mode
     *  The acl mode to check, i.e. "select", "insert", "update", or "delete".
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if access is granted, false otherwise.
     */
    public function has_access($id, $id_page, $mode, $is_group = false)
    {
        //if(!$is_group && $id == ADMIN_USER_ID) // why?
        //    return true;
        $acl = $this->get_access_levels($id, $id_page, $is_group);
        if(isset($acl[$mode]))
            return $acl[$mode];
        return false;
    }

    /**
     * Verifies user delete access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if access is granted, false otherwise.
     */
    public function has_access_delete($id, $id_page, $is_group = false)
    {
        return $this->has_access($id, $id_page, "delete", $is_group);
    }

    /**
     * Verifies user insert access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if access is granted, false otherwise.
     */
    public function has_access_insert($id, $id_page, $is_group = false)
    {
        return $this->has_access($id, $id_page, "insert", $is_group);
    }

    /**
     * Verifies user select access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if access is granted, false otherwise.
     */
    public function has_access_select($id, $id_page, $is_group = false)
    {
        return $this->has_access($id, $id_page, "select", $is_group);
    }

    /**
     * Verifies user update access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if access is granted, false otherwise.
     */
    public function has_access_update($id, $id_page, $is_group = false)
    {
        return $this->has_access($id, $id_page, "update", $is_group);
    }

    /**
     * Revokes user delete access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function revoke_access_delete($id, $id_page, $is_group = false)
    {
        return $this->set_access_levels($id, $id_page,
            array("delete" => false), $is_group);
    }

    /**
     * Revokes user insert access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function revoke_access_insert($id, $id_page, $is_group = false)
    {
        return $this->set_access_levels($id, $id_page,
            array("insert" => false), $is_group);
    }

    /**
     * Revokes user access to a specific page for all access levels starting
     * from a specified level.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param int $level
     *  The access level. The access levels are ordered as follows:
     *  1. select
     *  2. insert
     *  3. update
     *  4. delete
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function revoke_access_levels($id, $id_page, $level,
        $is_group = false)
    {
        if($level < 1) $level = 1;
        $acl = array();
        switch($level)
        {
            case 1: $acl['select'] = false;
            case 2: $acl['insert'] = false;
            case 3: $acl['update'] = false;
            case 4:
                $acl['delete'] = false;
                break;
            default: return true;;
        }
        return $this->set_access_levels($id, $id_page, $acl, $is_group);
    }

    /**
     * Revokes user select access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function revoke_access_select($id, $id_page, $is_group = false)
    {
        return $this->set_access_levels($id, $id_page,
            array("select" => false), $is_group);
    }

    /**
     * Revokes user update access to a specific page.
     *
     * @param int $id
     *  The unique identifier of the user or the group.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param bool $is_group
     *  If set to true, target the groups acl, otherwise the users acl.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function revoke_access_update($id, $id_page, $is_group = false)
    {
        return $this->set_access_levels($id, $id_page,
            array("update" => false), $is_group);
    }
}
?>
