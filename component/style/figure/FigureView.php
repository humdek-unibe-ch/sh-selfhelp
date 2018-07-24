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

    private function output_caption()
    {
        $caption_text = $this->model->get_db_field("caption");
        if($caption_text == "") return;
        $caption_title = "Abbildung";
        $parsedown = new parsedown();
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
        $url = BASE_PATH . $this->model->get_db_field("source");
        $alt = $this->model->get_db_field("alt");
        require __DIR__ . "/tpl_figure.php";
    }
}
?>
