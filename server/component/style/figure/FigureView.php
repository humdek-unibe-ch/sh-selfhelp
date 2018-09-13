<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the figure style component.
 */
class FigureView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title' (empty string).
     * The title of the figure. This is displayed when hovering over the figure.
     */
    private $title;

    /**
     * DB field 'source' (empty string).
     * The file name of the figure. If left empty, the figure is not rendered.
     */
    private $source;

    /**
     * DB field 'alt' (empty string).
     * The string to be displayed if the file is not found.
     */
    private $alt;

    /**
     * DB field 'caption' (empty string).
     * The caption to be placed below the figure. If not set, the caption will
     * not be rendered.
     */
    private $caption;

    /**
     * DB style field 'caption_title' (empty string).
     * The title of the caption.
     */
    private $caption_title;

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
        $this->title = $this->model->get_db_field("title");
        $this->source = $this->model->get_db_field("source");
        $this->alt = $this->model->get_db_field("alt");
        $this->caption = $this->model->get_db_field("caption");
        $this->caption_title = $this->model->get_db_field("caption_title");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the caption of a figure if it is available.
     */
    private function output_caption()
    {
        if($this->caption == "") return;
        require __DIR__ . "/tpl_caption.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->source == "") return;
        $url = ASSET_PATH . $this->source;
        require __DIR__ . "/tpl_figure.php";
    }
}
?>
