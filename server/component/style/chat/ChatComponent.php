<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/ChatViewSubject.php";
require_once __DIR__ . "/ChatViewTherapist.php";
require_once __DIR__ . "/ChatModelSubject.php";
require_once __DIR__ . "/ChatModelTherapist.php";
require_once __DIR__ . "/ChatController.php";

/**
 * The chat component.
 */
class ChatComponent extends BaseComponent
{
    private $db;
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The id of the section id of the chat component.
     * @param array $params
     *  The GET parameters of the contact page
     *   'uid': The id of the selected user to communicate with
     */
    public function __construct($services, $id, $params)
    {
        $this->db = $services['db'];
        $uid = isset($params['uid']) ? intval($params['uid']) : null;
        $gid = isset($params['gid']) ? intval($params['gid']) : null;
        $is_therapist = $this->check_experimenter_relation($_SESSION['id_user']);
        if($is_therapist)
            $model = new ChatModelTherapist($services, $id, $gid, $uid);
        else
            $model = new ChatModelSubject($services, $id, $gid);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new ChatController($model);
        if($is_therapist)
            $view = new ChatViewTherapist($model, $controller);
        else
            $view = new ChatViewSubject($model, $controller);
        parent::__construct($model, $view, $controller);
    }

    /**
     * Check whether a user is part of the experimenter group.
     *
     * @param int $aid
     *  The id of the user to check.
     * @retval array
     *  True if the user is part of the experimneter group, false otherwise.
     */
    private function check_experimenter_relation($uid)
    {
        $sql = "SELECT * FROM users_groups
            WHERE id_users = :uid AND id_groups = :gid";
        $res = $this->db->query_db_first($sql, array(
            ":uid" => $uid,
            ":gid" => EXPERIMENTER_GROUP_ID,
        ));
        if($res) return true;
        else return false;
    }
}
?>
