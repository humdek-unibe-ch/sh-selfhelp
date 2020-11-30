<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the carousel style component.
 * A carousel is a style that allows to show images as a slideshow.
 */
class CarouselView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'has_controls' (true).
     * If set to true, the carousel shows controls to navigate the images.
     */
    private $has_controls;

    /**
     * DB field 'has_indicators' (false).
     * If set to true, the carousel shows image indicators at the bottom.
     */
    private $has_indicators;

    /**
     * DB field 'has_crossfade' (false).
     * If set to true, the the images do not slide but fade from one to another.
     */
    private $has_crossfade;

    /**
     * DB field 'id_prefix' (empty string).
     * An string which will be prefixed to the carousel html id.
     */
    private $id_prefix;

    /**
     * DB field 'sources' (empty string)
     * A list of image sources. This is an array of json objects where each
     * object has the fileds 'source' and 'alt'.
     * If 'source' is not set, the image is not rendered.
     */
    private $sources;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->has_indicators = $this->model->get_db_field("has_indicators", false);
        $this->has_controls = $this->model->get_db_field("has_controls", true);
        $this->has_crossfade = $this->model->get_db_field("has_crossfade", false);
        $this->id_prefix = $this->model->get_db_field("id_prefix");
        $this->sources = $this->model->get_db_field("sources");
    }

    /* Private Methods ********************************************************/

    /**
     * Render the wrapper for the indicators.
     */
    private function output_indicator_wrapper()
    {
        if($this->has_indicators)
            require __DIR__ . '/tpl_indicators.php';
    }

    /**
     * Render the indicators (a small bar at the bottom of the carousel)
     */
    private function output_indicators()
    {
        $img_count = count($this->sources);
        for($i = 0; $i < $img_count; $i++)
        {
            $active = ($i === 0 ) ? "active" : "";
            require __DIR__ . '/tpl_indicator.php';
        }
    }

    /**
     * Render the image caption.
     *
     * @param string $caption
     *  The caption string.
     */
    private function output_caption($caption)
    {
        if($caption === null || $caption === "") return;
        require __DIR__ . '/tpl_caption.php';
    }

    /**
     * Render the carousel items.
     */
    private function output_carousel_items()
    {
        if($this->sources === "") return;
        $first = true;
        foreach($this->sources as $item)
        {
            if(!isset($item['source'])) continue;
            if(filter_var($item['source'], FILTER_VALIDATE_URL))
                $url = $item['source'];
            else
                $url = ASSET_PATH . '/' . $item['source'];
            $alt = $item['alt'] ?? "";
            $active = $first ? "active" : "";
            $first = false;
            $caption = $item['caption'] ?? null;
            require __DIR__ . '/tpl_carousel_item.php';
        }
    }

    /**
     * Render the carousel controls.
     */
    private function output_controls()
    {
        if($this->has_controls)
        {
            $direction = "prev";
            $icon = "fa-chevron-left";
            require __DIR__ . '/tpl_control.php';
            $direction = "next";
            $icon = "fa-chevron-right";
            require __DIR__ . '/tpl_control.php';
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $crossfade = $this->has_crossfade ? "carousel-fade" : "";
        require __DIR__ . "/tpl_carousel.php";
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
