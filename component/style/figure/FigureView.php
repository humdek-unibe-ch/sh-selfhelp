<?php
require_once __DIR__ . "/../../BaseView.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/service/Parsedown.php";

/**
 * The view class of the figure style component.
 */
class FigureView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the caption of a figure if it is available.
     */
    private function output_caption()
    {
        $caption_text = $this->model->get_db_field("caption");
        if($caption_text == "") return;
        $caption_title = $this->model->get_db_field("caption_title");
        $parsedown = new Parsedown();
        $caption = strip_tags($parsedown->text($caption_text), "<strong><em>");
        require __DIR__ . "/tpl_caption.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $title = $this->model->get_db_field("title");
        $url = ASSET_PATH . $this->model->get_db_field("source");
        $alt = $this->model->get_db_field("alt");
        require __DIR__ . "/tpl_figure.php";
    }
}
?>
