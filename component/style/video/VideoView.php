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
        if(!is_array($this->sources)) return;
        foreach($this->sources as $source)
        {
            if(!isset($source["source"]) || !isset($source["type"])) continue;
            $url = ASSET_PATH . $source["source"];
            $type = $source["type"];
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
