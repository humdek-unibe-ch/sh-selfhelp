<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/RegisterView.php";
require_once __DIR__ . "/RegisterModel.php";
require_once __DIR__ . "/RegisterController.php";

/**
 * The register component.
 *
 * It has a very simple model where page fields are fetched from the database
 * (no sections). What makes this component special is the controller and,
 * consequently, the view that is depending on the controller.
 */
class RegisterComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model, Controller and View
     * class and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param int $id
     *  The section id of this registe component instance.
     */
    public function __construct($services, $id)
    {
        $model = new RegisterModel($services, $id);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new RegisterController($model);
        $view = new RegisterView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
