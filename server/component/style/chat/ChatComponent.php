<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/ChatView.php";
require_once __DIR__ . "/ChatModel.php";
require_once __DIR__ . "/ChatController.php";

/**
 * The chat component.
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
     *  The GET parameters of the contact page
     *   'uid': The id of the selected user to communicate with
     */
    public function __construct($services, $id, $params)
    {
        $uid = isset($params['uid']) ? intval($params['uid']) : null;
        $model = new ChatModel($services, $id, $uid);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new ChatController($model);
        $view = new ChatView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
