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
        $this->text = $this->model->get_db_field("text");
        $this->text = htmlspecialchars($this->text ? $this->text : '');
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    // css/rawText.css is loaded like the css of every style: styles.min.css, or one by one with DEBUG
    // (BasePage::collect_style_includes).

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_raw_text.php";
    }

}
?>
