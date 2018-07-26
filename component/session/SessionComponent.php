<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/SessionView.php";
require_once __DIR__ . "/SessionModel.php";

/**
 * A component to for a single, generic session
 */
class SessionComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the SessionModel class and the
     * SessionView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param object $router
     *  The router instance which is used to generate valid links.
     * @param object $db
     *  The db instance which grants access to the DB.
     * @param int $id
     *  The section id of this session.
     * @param object $nav
     *  The session navigation component.
     */
    public function __construct($services, $id)
    {
        $model = new SessionModel($services, $id);
        $view = new SessionView($model, $services['nav']);
        parent::__construct($view);
    }
}
?>
