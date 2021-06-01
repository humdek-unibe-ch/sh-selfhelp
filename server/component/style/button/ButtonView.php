<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the button style component.
 * This style components allows to represent a link as a button.
 */
class ButtonView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'label' (empty string).
     * The label on the button. If this field is not set, the button is not
     * rendered.
     */
    private $label;

    /**
     * DB field 'url' (empty string).
     * The target url of the button. If this field is not set, the button is not
     * rendered.
     */
    private $url;

    /**
     * DB field 'type' ('primary').
     * The style of the button. E.g. 'warning', 'danger', etc.
     */
    private $type;

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
        $this->type = $this->model->get_db_field("type", "primary");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if ($this->url == "" || $this->label == "") return;
        require __DIR__ . "/tpl_button.php";
    }

    /**
     * Render output as an entry
     * @param array $entry_value
     * the data for the entry value 
     */
    public function output_content_entry($entry_value)
    {
        if ($this->url == "" || $this->label == "") return;
        $this->url = $this->model->get_db_field("url");
        $this->url = $this->get_entry_value($entry_value, $this->url); 
        require __DIR__ . "/tpl_button.php";
    }

    /**
     * Render output as an entry for mobile
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_content_mobile_entry($entry_value)
    {
        $style = parent::output_content_mobile();
        $this->url = $this->model->get_db_field("url");
        $this->url = $this->get_entry_value($entry_value, $this->url); 
        $style['url']['content'] = $this->url[0] == '/' ? $this->url : '/' . $this->url;
        return $style;
    }
}
?>
