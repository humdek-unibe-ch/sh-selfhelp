<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the video source style component.
 */
class VideoSourceView extends BaseView
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

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $url = ASSET_PATH . $this->model->get_db_field("source");
        $type = $this->model->get_db_field("type");
        require __DIR__ . "/tpl_video_source.php";
    }
}
?>
