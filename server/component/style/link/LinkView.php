<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the link style component.
 */
class LinkView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'url' (empty string).
     * The target of the link. If the url is not set, the link will not be
     * rendered.
     */
    private $url;

    /**
     * DB field 'label' (empty string).
     * The name that will be displayed. If the name is not set, the url is used
     * as name.
     */
    private $label;

    /**
     * DB field 'open_in_new_tab' (false).
     * If set to true, the link is opened in a new tab or window. If set to
     * false the link is opened in the current context.
     */
    private $open_in_new_tab;

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
        $this->label = $this->model->get_db_field("label");
        $this->url = $this->model->get_db_field("url");
        $this->open_in_new_tab = $this->model->get_db_field("open_in_new_tab",
            false);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->url == "") return;
        if($this->label == "") $this->label = htmlspecialchars($this->url);
        $target = ($this->open_in_new_tab) ? 'target="_blank"' : "";
        require __DIR__ . "/tpl_link.php";
    }
}
?>
