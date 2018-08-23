<?php
require_once __DIR__ . "/../../BaseModel.php";
/**
 * This class is used to prepare all data related to the group component such
 * that the data can easily be displayed in the view of the component.
 */
class GroupModel extends BaseModel
{
    /* Private Properties *****************************************************/

    private $groups;
    private $selected_group;
    private $gid;

    /* Constructors ***********************************************************/

    /**
     * The constructor fetches all login related fields from the database.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $gid)
    {
        parent::__construct($services);
        $this->gid = $gid;
        $this->selected_group = null;
        if($gid != null) $this->selected_group = $this->fetch_group($gid);
        $this->groups = array();
        $this->set_groups($this->fetch_groups());
    }

    /* Private Methods ********************************************************/

    private function fetch_group($gid)
    {
        $sql = "SELECT g.id, g.name FROM groups AS g
            WHERE g.id = :gid";
        return $this->db->query_db_first($sql, array("gid" => $gid));
    }

    private function fetch_group_users($gid)
    {
        $sql = "SELECT u.id, u.email AS title FROM users AS u
            LEFT JOIN users_groups AS ug ON ug.id_users = u.id
            WHERE ug.id_groups = :gid";
        return $this->db->query_db($sql, array("gid" => $gid));
    }

    private function fetch_groups()
    {
        $sql = "SELECT g.id, g.name FROM groups AS g
            ORDER BY g.name";
        return $this->db->query_db($sql);
    }

    private function fetch_acl_by_group($gid)
    {
        $acl = array();
        $sql = "SELECT p.id, p.keyword FROM pages AS p ORDER BY p.keyword";
        $pages = $this->db->query_db($sql);
        foreach($pages as $page)
        {
            $pid = intval($page['id']);
            $acl[$page['keyword']] = array(
                $this->acl->has_access_select($gid, $pid, true),
                $this->acl->has_access_insert($gid, $pid, true),
                $this->acl->has_access_update($gid, $pid, true),
                $this->acl->has_access_delete($gid, $pid, true)
            );
        }
        return $acl;
    }

    private function set_groups($groups)
    {
        foreach($groups as $group)
        {
            $id = intval($group["id"]);
            $this->groups[] = array(
                "id" => $id,
                "title" => $group["name"],
                "url" => $this->get_link_url("group", array("id" => $id))
            );
        }
    }

    /* Public Methods *********************************************************/

    public function get_selected_group()
    {
        return $this->selected_group;
    }

    public function get_groups()
    {
        return $this->groups;
    }

    public function get_acl_selected_group()
    {
        return $this->fetch_acl_by_group($this->gid);
    }

    public function get_selected_group_users()
    {
        return $this->fetch_group_users($this->gid);
    }
}
?>
