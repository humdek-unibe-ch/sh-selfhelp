<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/ChatViewSubject.php";
require_once __DIR__ . "/ChatViewTherapist.php";
require_once __DIR__ . "/ChatModelSubject.php";
require_once __DIR__ . "/ChatModelTherapist.php";
require_once __DIR__ . "/ChatController.php";

/**
 * The chat component. Note that while the chat is a style and can be used on
 * any page it requires certain GET parameters to work properly (see
 * constructor).
 */
class ChatComponent extends BaseComponent
{
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
     *  The GET parameters of the chatTherapist or chatSubject page
     *   'uid': The id of the selected user to communicate with
     *   'gid': The id of the selected group to communicate with
     *   'chrid': The id of the selected chat group to communicate with
     */
    public function __construct($services, $id, $params)
    {
        $is_therapist = $services->get_acl()->has_access_select($_SESSION['id_user'], $services->get_db()->fetch_page_id_by_keyword('chatTherapist'));
        $uid = isset($params['uid']) ? intval($params['uid']) : null;
        $gid = isset($params['gid']) ? intval($params['gid']) : 0;
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

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Checks whether the current user is allowed to access the active group.
     *
     * @retval bool
     *  True if access is granted, false otherwise.
     */
    public function has_access()
    {
        return parent::has_access();
    }
}
?>
