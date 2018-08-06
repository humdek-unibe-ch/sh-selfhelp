<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the video style component.
 */
class VideoView extends BaseView
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
     * Render all video sources of a video.
     */
    private function output_video_sources()
    {
        $sources = explode(',', $this->model->get_db_field("sources"));
        foreach($sources as $source)
        {
            $items = explode('#', $source);
            $url = ASSET_PATH . $items[0];
            $type = $items[1];
            require __DIR__ . "/tpl_video_source.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $alt = $this->model->get_db_field("alt");
        require __DIR__ . "/tpl_video.php";
    }
}
?>
