<?php
require_once __DIR__ . "/../formBase/FormBaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the form style component. This component renders a html
 * form.
 */
class FormLogView extends FormBaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $children = $this->model->get_children();
        $form = new BaseStyleComponent("form", array(
            "label" => $this->label,
            "type" => $this->type,
            "url" => $_SERVER['REQUEST_URI'],
            "children" => $children,
        ));
        require __DIR__ . "/tpl_form_log.php";
    }
}
?>
