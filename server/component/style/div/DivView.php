<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the div style component.
 * A div style is a container that allows to wrap content into a div tag.
 */
class DivView extends StyleView
{
    /* Constructors ***********************************************************/

    /**
     * Id used for html element
     */
    private $id;

    /**
     * Custom inline style
     */
    private $style = '';

    /**
     * Custom background color
     */
    private $color_background;

    /**
     * Custom border color. If it is set, a border is added
     */
    private $color_border;

    /**
     * Custom color used for the text
     */
    private $color_text;

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->id = $this->model->get_db_field("id", $this->id_section);
        $this->color_background = $this->model->get_db_field("color_background", '');
        $this->color_border = $this->model->get_db_field("color_border", '');
        $this->color_text = $this->model->get_db_field("color_text", '');
        if ($this->color_background) {
            $this->style = 'background-color: ' .   $this->color_background . '; ';
        }
        if ($this->color_text) {
            $this->style = $this->style . 'color: ' .   $this->color_text . '; ';
        }
        if ($this->color_border) {
            $this->style = $this->style . 'border-color: ' .   $this->color_border . '; ';
            $this->css = 'border ' . $this->css; // add class for border, if a border color is set
            $this->css_mobile = 'border ' . $this->css_mobile; // add class for border, if a border color is set
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_div.php";
    }
	
}
?>
