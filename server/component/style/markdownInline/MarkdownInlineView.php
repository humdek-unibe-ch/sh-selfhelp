<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the markdown inline component.
 * This style component does the same as the markdown style however is limited
 * one-line statemenst.
 */
class MarkdownInlineView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'text_md_inline' (empty string).
     * The text to be rendered as markdown content.
     */
    private $text_md_inline;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->text_md_inline = $this->model->get_db_field('text_md_inline');
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        if(is_a($this->model, "BaseStyleModel"))
        {
            $pd = new ParsedownExtension();
            $md = $pd->line($this->text_md_inline);
        }
        else
            $md = $this->text_md_inline;
        require __DIR__ . "/tpl_markdown.php";
    }

    /**
     * Render output as an entry
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_content_entry($entry_value)
    {
        $this->text_md_inline = str_replace('<p>', '', $this->text_md_inline);
        $this->text_md_inline = str_replace('</p>', '', $this->text_md_inline);
        $txt = $this->get_entry_value($entry_value, $this->text_md_inline); 
        if(is_a($this->model, "BaseStyleModel"))
        {
            $pd = new ParsedownExtension();
            $md = $pd->line($txt);
        }
        else
            $md = $txt;
        require __DIR__ . "/tpl_markdown.php";
    }

    /**
     * Render output as an entry for mobile
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_content_mobile_entry($entry_value)
    {
        $style = parent::output_content_mobile();
        $this->text_md_inline = str_replace('<p>', '', $this->text_md_inline);
        $this->text_md_inline = str_replace('</p>', '', $this->text_md_inline);
        $txt = $this->get_entry_value($entry_value, $this->text_md_inline);
        if (is_a($this->model, "BaseStyleModel")) {
            $pd = new ParsedownExtension();
            $md = $pd->line($txt);
        } else
            $md = $txt;
        $style['text_md_inline']['content'] = $md;
        return $style;
    }
	
}
?>
