<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/BaseStyleComponent.php";

/**
 * The view class of the style component.
 */
class StyleView extends BaseView
{
    /* Private Properties *****************************************************/

    private $fluid;
    private $style;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     * @param bool $fluid
     *  If set to true the content will be rendered in a container-fluid
     *  bootstrap element, if set to false in a container.
     */
    public function __construct($model, $fluid=false)
    {
        parent::__construct($model);
        $this->style = new BaseStyleComponent(
            $this->model->get_style_name(),
            $this->model->get_db_fields(),
            $fluid);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $this->style->output_content();
    }

    /**
     * Get css include files required for this component. This overrides the 
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes()
    {
        return $this->style->get_css_includes();
    }
}
?>
