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
    }

    /** Private Methods */

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
        $md = $this->balance_html_fragment($md);
        require __DIR__ . "/tpl_markdown.php";
    }

    /**
     * Auto-close unclosed tags in a raw HTML fragment.
     * Authors can embed HTML in markdown; a missing </div> otherwise breaks
     * everything that follows in the page (including the CMS chrome).
     *
     * @param string $html
     * @return string
     */
    private function balance_html_fragment($html)
    {
        if ($html === null || $html === '' || strpos($html, '<') === false) {
            return $html;
        }
        $prev = libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $loaded = $doc->loadHTML(
            '<?xml encoding="UTF-8"><div id="sh-html-root">' . $html . '</div>',
            LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        if (!$loaded) {
            return $html;
        }
        $root = $doc->getElementById('sh-html-root');
        if (!$root) {
            return $html;
        }
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        return $out;
    }

}
?>
