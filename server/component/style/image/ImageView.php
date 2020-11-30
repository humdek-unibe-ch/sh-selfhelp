<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the image style component.
 * This style component allows to display image sources.
 */
class ImageView extends StyleView
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
     * DB field 'is_fluid' (true).
     * If set to true the class img-fluid is assigned to the image.
     */
    private $is_fluid;

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
        $this->is_fluid = $this->model->get_db_field("is_fluid", true);
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
            $url = ASSET_PATH . '/' . $this->source;
        $fluid = $this->is_fluid ? "img-fluid" : "";
        require __DIR__ . "/tpl_image.php";
    }
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
