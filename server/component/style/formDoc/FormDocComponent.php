<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/FormDocView.php";
require_once __DIR__ . "/../formBase/FormBaseModel.php";
require_once __DIR__ . "/../formBase/FormBaseController.php";

/**
 * A component class for a formDoc styel component. This style is intended to
 * handle persistent user input. Persisten user input is stored to the database
 * and made availabile to the user for continuous modification.
 */
class FormDocComponent extends BaseComponent
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
        $model = new FormBaseModel($services, $id);
        $controller = null;
        if(!$model->is_cms_page())
            $controller = new FormBaseController($model);
        $view = new FormDocView($model, $controller);
        parent::__construct($model, $view, $controller);

        $this->propagate_input_field_settings($this->get_children());
    }

    /* Private Methods ********************************************************/

    /**
     * For each child of style formField enable the setting that the last db
     * entry is displayed in the form field. This is a recursive method.
     *
     * @param array $children
     *  The child component array of the current component.
     */
    private function propagate_input_field_settings($children)
    {
        foreach($children as $child)
        {
            $style = $child->get_style_instance();
            if(is_a($style, "FormFieldComponent"))
            {
                $style->enable_show_db_value();
                $style->enable_user_input();
            }
            $this->propagate_input_field_settings($child->get_children());
        }
    }
}
?>
