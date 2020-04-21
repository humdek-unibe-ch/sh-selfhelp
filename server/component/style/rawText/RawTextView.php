<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the raw text style component. This component renders text
 * into a pre and code tag.
 */
class RawTextView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'text' (empty string).
     * The text to be rendered.
     */
    private $text;

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
        $this->text = htmlspecialchars($this->model->get_db_field("text"));
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/rawText.css");
        return parent::get_css_includes($local);
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_raw_text.php";
    }
}
?>
