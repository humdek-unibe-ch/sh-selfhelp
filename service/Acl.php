<?php
/**
 * This class handles the Access Control Layer (ACL).
 */
class Acl
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
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
    private function get_access_levels_db($id_user, $id_page)
    {
        $sql = "SELECT * FROM acl_users
            WHERE id_users = :uid AND id_pages = :pid";
        $arguments = array(
            ":uid" => $id_user,
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
     *  An associative array with the acces rights: 'select', 'insert',
     *  'update', and 'delete'.
     */
    private function get_access_levels($id_user, $id_page)
    {
        $acl_db = $this->get_access_levels_db($id_user, $id_page);
        $acl = array(
            "select" => false,
            "insert" => false,
            "update" => false,
            "delete" =>false
        );
        if($acl_db)
        {
            $acl["select"] = ($acl_db["acl_select"] == '1') ? true : false;
            $acl["insert"] = ($acl_db["acl_insert"] == '1') ? true : false;
            $acl["update"] = ($acl_db["acl_update"] == '1') ? true : false;
            $acl["delete"] = ($acl_db["acl_delete"] == '1') ? true : false;
        }
        return $acl;
    }

    /**
     * Connects to the database and sets the acces rights of a user for a
     * specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param array
     *  An associative array with the acces rights to set where the keys are:
     *  'select', 'insert', 'update', and 'delete'.
     * @retval bool
     *  true if successful, false otherwise.
     */
    private function set_access_levels($id_user, $id_page, $acl)
    {
        $acl_db = array();
        foreach($acl as $key => $value)
            $acl_db["acl_" . $key] = ($value) ? '1' : '0';

        $ids = array(
            "id_users" => $id_user,
            "id_pages" => $id_page
        );

        if($this->get_access_levels_db($id_user, $id_page))
        {
            if($this->db->update_by_ids("acl", $acl_db, $ids))
                return true;
        }
        else
        {
            if($this->db->insert("acl", array_merge($acl_db, $ids)))
                return true;
        }
        return false;
    }

    /**
     * Grants the user access to a specific page for all access levels up to a
     * specified level.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param int $level
     *  The access level. The access levels are ordered as follows:
     *  1. select
     *  2. insert
     *  3. update
     *  4. delete
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function grant_access_levels($id_user, $id_page, $level)
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
        return $this->set_access_levels($id_user, $id_page, $acl);
    }

    /**
     * Revokes user access to a specific page for all access levels starting
     * from a specified level.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @param int $level
     *  The access level. The access levels are ordered as follows:
     *  1. select
     *  2. insert
     *  3. update
     *  4. delete
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function revoke_access_levels($id_user, $id_page, $level)
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
        return $this->set_access_levels($id_user, $id_page, $acl);
    }

    /**
     * Grants the user select access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function grant_access_select($id_user, $id_page)
    {
        return $this->set_access_levels($id_user, $id_page,
            array("select" => true));
    }

    /**
     * Verifies user select access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if access is granted, false otherwise.
     */
    public function has_access_select($id_user, $id_page)
    {
        $acl = $this->get_access_levels($id_user, $id_page);
        return $acl["select"];
    }

    /**
     * Revokes user select access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function revoke_access_select($id_user, $id_page)
    {
        return $this->set_access_levels($id_user, $id_page,
            array("select" => false));
    }

    /**
     * Grants the user insert access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function grant_access_insert($id_user, $id_page)
    {
        return $this->set_access_levels($id_user, $id_page,
            array("insert" => true));
    }

    /**
     * Verifies user insert access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if access is granted, false otherwise.
     */
    public function has_access_insert($id_user, $id_page)
    {
        $acl = $this->get_access_levels($id_user, $id_page);
        return $acl["insert"];
    }

    /**
     * Revokes user insert access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function revoke_access_insert($id_user, $id_page)
    {
        return $this->set_access_levels($id_user, $id_page,
            array("insert" => false));
    }

    /**
     * Grants the user update access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function grant_access_update($id_user, $id_page)
    {
        return $this->set_access_levels($id_user, $id_page,
            array("update" => true));
    }

    /**
     * Verifies user update access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if access is granted, false otherwise.
     */
    public function has_access_update($id_user, $id_page)
    {
        $acl = $this->get_access_levels($id_user, $id_page);
        return $acl["update"];
    }

    /**
     * Revokes user update access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function revoke_access_update($id_user, $id_page)
    {
        return $this->set_access_levels($id_user, $id_page,
            array("update" => false));
    }

    /**
     * Grants the user delete access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function grant_access_delete($id_user, $id_page)
    {
        return $this->set_access_levels($id_user, $id_page,
            array("delete" => true));
    }

    /**
     * Verifies user delete access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if access is granted, false otherwise.
     */
    public function has_access_delete($id_user, $id_page)
    {
        $acl = $this->get_access_levels($id_user, $id_page);
        return $acl["delete"];
    }

    /**
     * Revokes user delete access to a specific page.
     *
     * @param int $id_user
     *  The unique identifier of the user.
     * @param in $id_page
     *  The unique identifier of the page.
     * @retval bool
     *  true if successful, false otherwise.
     */
    public function revoke_access_delete($id_user, $id_page)
    {
        return $this->set_access_levels($id_user, $id_page,
            array("delete" => false));
    }
}
?>
