<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the video style component.
 */
class VideoView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB style field 'alt'
     * The text to be rendered if video playback is not supported by the
     * browser.
     */
    private $alt;

    /**
     * DB field 'sources' (empty string)
     * A list of video sources. To format must be of the form
     * <source_1>#<type_source_1>,<source_2>#<type_source_2>,...
     * If this is not set, the video component is not rendered.
     */
    private $sources;

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
        $this->alt = $this->model->get_db_field("alt");
        $this->sources = $this->model->get_db_field("sources");
    }

    /* Private Methods ********************************************************/

    /**
     * Render all video sources of a video.
     */
    private function output_video_sources()
    {
        $sources = explode(',', $this->sources);
        foreach($sources as $source)
        {
            $items = explode('#', $source);
            if(count($items) <= 1) continue;
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
        if($this->sources == "") return;
        require __DIR__ . "/tpl_video.php";
    }
}
?>
