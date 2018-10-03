<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the style component. Each style is wrapped in a div
 * container which serves to identify styles by id. This feature is used in the
 * CMS to highlight the selected style.
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
     */
    public function __construct($model, $style)
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
