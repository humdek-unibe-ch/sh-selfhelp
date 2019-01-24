<?php
require_once __DIR__ . "/../chatAdmin/ChatAdminComponent.php";
require_once __DIR__ . "/../chatAdmin/ChatAdminModel.php";
require_once __DIR__ . "/ChatAdminSelectView.php";

/**
 * The chatAdmin select component.
 */
class ChatAdminSelectComponent extends ChatAdminComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'rid':     The id of the chat room that is currently edited.
     */
    public function __construct($services, $params)
    {
        $rid = isset($params['rid']) ? intval($params['rid']) : null;
        $model = new ChatAdminModel($services, $rid);
        $view = new ChatAdminSelectView($model);
        parent::__construct($model, $view);
    }
}
?>
