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
class HtmlTagView extends StyleView
{
    /* Constructors ***********************************************************/

    /**
     * Id used for html element
     */
    private $id;

    /**
     * The selected html tag for the style to represent
     */
    private $html_tag;

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
        $this->html_tag = $this->model->get_db_field("html_tag", null);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if (!$this->html_tag) {
            // if not set does no return anything
            return;
        }
        if (method_exists($this->model, 'is_cms_page_editing') && $this->model->is_cms_page_editing()) {
            require __DIR__ . "/tpl_htmlTag_cms.php";
        } else {
            require __DIR__ . "/tpl_htmlTag.php";
        }        
    }

    public function open_tag()
    {
        echo '<' . $this->html_tag . ' class="' . $this->css . '">';
    }
    public function close_tag()
    {
        echo '</' . $this->html_tag . '>';
    }
}
?>
