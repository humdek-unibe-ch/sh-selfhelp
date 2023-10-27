<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the figure style component.
 */
class FigureView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'caption' (empty string).
     * The caption to be placed below the figure. If not set, the caption will
     * not be rendered.
     */
    private $caption;

    /**
     * DB style field 'caption_title' ("Figure").
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
        $this->caption = $this->model->get_db_field("caption");
        $this->caption_title = $this->model->get_db_field("caption_title", "Figure");
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
        require __DIR__ . "/tpl_figure.php";
    }
	
}
?>
