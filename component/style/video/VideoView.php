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
        $children = $this->model->get_db_field("content");
        foreach($children as $child)
            $child->output_content();
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
