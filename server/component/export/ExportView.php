<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the export component.
 */
class ExportView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the export component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Private Methods ********************************************************/

    private function output_export_item($selector)
    {
        $fields = $this->model->get_export_view_fields($selector);
        $title = $fields["title"];
        $text = $fields["text"];
        $url = $fields["url"];
        $label = $fields["label"];
        require __DIR__ . "/tpl_export_item.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        $title = $this->model->get_title();
        $text = $this->model->get_text();
        require __DIR__ . "/tpl_export.php";
    }
}
?>
