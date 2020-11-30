<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the heading style component.
 * This style component allows to display html headings.
 */
class HeadingView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title' (empty string).
     * The text of the title to be rendered.
     */
    private $title;

    /**
     * DB field 'level' (1).
     * The html level of the title, i.e. a number in the interval [1, 6]
     */
    private $level;

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
        $this->level = $this->model->get_db_field("level", 1);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->level < 1) $this->level = 1;
        if($this->level > 6) $this->level = 6;
        require __DIR__ . "/tpl_title.php";
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
