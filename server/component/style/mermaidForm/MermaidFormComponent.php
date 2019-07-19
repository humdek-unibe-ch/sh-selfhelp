<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/MermaidFormView.php";
require_once __DIR__ . "/../formUserInput/FormUserInputModel.php";
require_once __DIR__ . "/../formUserInput/FormUserInputController.php";

/**
 * A component class for a MermaidForm style component. This style is intended
 * to handle user input.
 *
 * - Persisten user input is stored to the database and made availabile to the
 * user for continuous modification.
 * - Journal user input is stored to the database together with a timestamp and
 * cannot be changed afterwards.
 */
class MermaidFormComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes the view instance to the constructor of the parent
     * class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition basepage for a list of all services.
     * @param int $id
     *  The section id of this navigation component.
     */
    public function __construct($services, $id)
    {
        $model = new FormUserInputModel($services, $id);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new FormUserInputController($model);
        $view = new MermaidFormView($model, $controller);

        parent::__construct($model, $view, $controller);
    }
}
?>
