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

        $style = new BaseStyleComponent($this->model->get_style_name(), $fluid);
        $this->add_local_component("style", $style,
            $this->model->get_db_fields());
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $this->output_local_component("style");
    }
}
?>
