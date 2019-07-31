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

    /**
     * Render an export item.
     *
     * @param string $selector
     *  The key to select the chosen data.
     */
    private function output_export_item($selector)
    {
        if(!$this->model->can_export_codes() && $selector === "validation_codes")
            return;
        $fields = $this->model->get_export_view_fields($selector);
        $title = $fields["title"];
        $text = $fields["text"];
        $options = $fields["options"];
        require __DIR__ . "/tpl_export_item.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the export item option.
     *
     * @param array $options
     *  An array of options where each option has the following keys:
     *  - `url`:    The url to the exported item.
     *  - `label`:  The label on the export button.
     *  - `type`:   The bootstrap type to color th ebutton.
     */
    public function output_export_item_options($options)
    {
        foreach($options as $option)
        {
            $url = $option['url'];
            $label = $option['label'];
            $type = $option['type'];
            require __DIR__ . "/tpl_export_item_option.php";
        }
    }

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
