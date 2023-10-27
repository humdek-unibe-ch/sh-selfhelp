<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the audio style component.
 */
class AudioView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'alt' (empty string)
     * The text to be rendered if audio playback is not supported by the
     * browser.
     */
    private $alt;

    /**
     * DB field 'sources' (empty string)
     * A list of audio sources. This is an array of json objects where each
     * object has the fileds 'source' and 'type'.
     * If this is not set, the audio component is not rendered.
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
     * Render all audio sources.
     */
    private function output_audio_sources()
    {
        if(!is_array($this->sources)) return;
        foreach($this->sources as $source)
        {
            if(!isset($source["source"]) || !isset($source["type"])) continue;
            $url = ASSET_PATH . '/' . $source["source"];
            $type = $source["type"];
            require __DIR__ . "/tpl_audio_source.php";
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->sources == "") return;
        require __DIR__ . "/tpl_audio.php";
    }

    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        $sources = [];
        if (is_array($this->sources)) {
            foreach ($this->sources as $source) {
                if (!isset($source["source"]) || !isset($source["type"])) continue;
                $curSource = [];
                $curSource['source'] = ASSET_FOLDER . '/' . $source["source"];
                $curSource['type'] = $source["type"];
                $sources[] = $curSource;
            }
        }
        $style['sources']['content'] = $sources;
        return $style;
    }
}
?>
