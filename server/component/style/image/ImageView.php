<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the image style component.
 */
class ImageView extends BaseView
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
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->source == "") return;
        if(filter_var($this->source, FILTER_VALIDATE_URL))
            $url = $this->source;
        else
            $url = ASSET_PATH . $this->source;
        require __DIR__ . "/tpl_image.php";
    }
}
?>
