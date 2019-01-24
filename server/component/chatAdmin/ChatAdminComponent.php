<?php
require_once __DIR__ . "/../BaseComponent.php";

/**
 * The base chatAdmin component.
 */
class ChatAdminComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor. It passes the view and controller instance to the
     * constructor of the parent class.
     *
     * @param object $model
     *  The model instance of the view component.
     * @param object $view
     *  The view instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $view, $controller=null)
    {
        parent::__construct($model, $view, $controller);
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function to deny access on invalid chat rooms.
     *
     * @retval bool
     *  True if the group exists, false otherwise
     */
    public function has_access()
    {
        return parent::has_access();
    }
}
?>
