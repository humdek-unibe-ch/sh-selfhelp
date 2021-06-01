<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the markdown component.
 * This style component is one of the main styles to produce content. This
 * allows to display markdown content.
 */
class MarkdownView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'text_md' (empty string).
     * The text to be rendered as markdown content.
     */
    private $text_md;

    /**
     * DB field 'data_config' (empty string).
     * If some value is loaded from the upload tables
     */
    private $data_config;

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
        $this->text_md = $this->model->get_db_field('text_md');
        $this->data_config = $this->model->get_db_field("data_config");
        if ($this->data_config) {
            $this->retrieve_data();
        }
    }

    /** Private Methods */

    /**
     * Retrieve data from database - base dont the JSON configuration
     */
    private function retrieve_data()
    {
        $fields = $this->model->retrieve_data($this->data_config);
        if ($fields) {
            foreach ($fields as $field_name => $field_value) {
                $this->text_md = str_replace($field_name, $field_value, $this->text_md);
            }
        } else {
            $this->text_md = '';
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        if (is_a($this->model, "BaseStyleModel")) {
            $pd = new ParsedownExtension();
            $md = $pd->text($this->text_md);
        } else
            $md = $this->text_md;
        require __DIR__ . "/tpl_markdown.php";
    }

    /**
     * Render output as an entry
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_content_entry($entry_value)
    {
        $this->text_md = str_replace('<p>', '', $this->text_md);
        $this->text_md = str_replace('</p>', '', $this->text_md);
        $txt = $this->get_entry_value($entry_value, $this->text_md); 
        $pd = new ParsedownExtension();
        $md = $pd->text($txt);
        require __DIR__ . "/tpl_markdown.php";
    }

    public function output_content_mobile()
    {
        $style = parent::output_content_mobile();
        if ($this->data_config) {
            $this->retrieve_data();
            $style['text_md']['content'] = $this->text_md;
        }
        return $style;
    }

    /**
     * Render output as an entry for mobile
     * @param array $entry_value
     * the data for the entry value
     */
    public function output_content_mobile_entry($entry_value)
    {
        $style = parent::output_content_mobile();
        $this->text_md = str_replace('<p>', '', $this->text_md);
        $this->text_md = str_replace('</p>', '', $this->text_md);
        $txt = $this->get_entry_value($entry_value, $this->text_md); 
        $pd = new ParsedownExtension();
        $md = $pd->text($txt);
        $style['text_md']['content'] = $md;
        if ($this->data_config) {
            $this->retrieve_data();
            $style['text_md']['content'] = $md;
        }
        return $style;
    }
}
?>
