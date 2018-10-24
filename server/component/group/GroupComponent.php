<?php
require_once __DIR__ . "/../BaseComponent.php";

/**
 * The base group component.
 */
class GroupComponent extends BaseComponent
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
     * Redefine the parent function to deny access on invalid groups.
     *
     * @retval bool
     *  True if the group exists, false otherwise
     */
    public function has_access()
    {
        $id = $this->model->get_gid();
        if($id != null
            && ($this->model->get_selected_group() == null)
                || !$this->model->is_group_allowed($id))
            return false;
        return parent::has_access();
    }
}
?>
