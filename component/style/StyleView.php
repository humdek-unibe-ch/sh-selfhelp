<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/BaseStyleComponent.php";

/**
 * The view class of the style component.
 */
class StyleView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     * @param object $style
     *  The style component to be rendered.
     * @param bool $fluid
     *  If set to true the content will be rendered in a container-fluid
     *  bootstrap element, if set to false in a container.
     */
    public function __construct($model, $style, $fluid=false)
    {
        parent::__construct($model);

        $this->add_local_component("style", $style);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $highlight = $this->model->get_db_field("is_active") ? "highlight" : "";
        $id = $this->model->get_db_field("id");
        require __DIR__ . "/tpl_style.php";
    }
}
?>
