<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/SessionView.php";
require_once __DIR__ . "/SessionModel.php";

/**
 * A component to for a single, generic session
 * A session style components supports the following fields:
 *  'title':
 *      The title of the session. This is a title style component.
 *  'content':
 *      A list of style components to fill up the content of the session.
 */
class SessionComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the SessionModel class and the
     * SessionView class and passes the view instance to the constructor of the
     * parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this session.
     */
    public function __construct($services, $id, $id_active=null)
    {
        $model = new SessionModel($services, $id, $id_active);
        $view = new SessionView($model);
        parent::__construct($view);
    }
}
?>
