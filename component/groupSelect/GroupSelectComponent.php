<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../group/GroupView.php";
require_once __DIR__ . "/../group/GroupComponent.php";
require_once __DIR__ . "/../group/GroupModel.php";

/**
 * The group select component.
 */
class GroupSelectComponent extends GroupComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the GroupModel and the
     * GroupSelectView and passes the view instance to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   'gid':     The id of the group that is currently edited.
     */
    public function __construct($services, $params)
    {
        $gid = isset($params['gid']) ? intval($params['gid']) : null;
        $model = new GroupModel($services, $gid);
        $view = new GroupView($model);
        parent::__construct($model, $view);
    }
}
?>
