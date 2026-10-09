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
     * Close the tags an author left open in a raw HTML fragment.
     * Authors can embed HTML in markdown; a missing </div> otherwise swallows
     * everything that follows in the page (including the CMS chrome).
     * Only the missing end tags are appended, the rest of the fragment is
     * returned byte for byte. It is deliberately not parsed and re-serialised:
     * DOMDocument rewrites URLs and attributes ({{placeholders}}, spaces and
     * umlauts in href/src, @click), cuts inline <script> text at the first "</"
     * and loses all content after a stray end tag.
     *
     * @param string $html
     * @return string
     */
    private function balance_html_fragment($html)
    {
        if ($html === null || $html === '' || strpos($html, '<') === false) {
            return $html;
        }
        // Elements without an end tag (html/head/body are ignored on purpose).
        $void = array_flip(['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta',
            'param', 'source', 'track', 'wbr', 'html', 'head', 'body']);
        // The browser closes these itself; an extra </p> would add an empty paragraph.
        $implied = array_flip(['p', 'li', 'dt', 'dd', 'tr', 'td', 'th', 'thead', 'tbody', 'tfoot',
            'option', 'optgroup', 'colgroup', 'caption']);
        $stack = [];
        $tail = '';
        $attrs = '(?:"[^"]*+"|\'[^\']*+\'|[^\'">])*+';
        $re = '~<!--[^-]*+(?:-(?!->)[^-]*+)*+(?:-->)?'                                  // comment
            . '|<(script|style|textarea|title|iframe)(?=[\s/>])' . $attrs . '>'         // text-only element ...
            . '[^<]*+(?:<(?!/\1[\s/>])[^<]*+)*+(</\1(?=[\s/>])[^>]*>)?'                  // ... up to its end tag
            . '|<(/?)([a-z][a-z0-9:_-]*)(' . $attrs . ')>~is';                          // start / end tag
        // The callback only collects the open tags; its (empty) result is not used.
        $done = preg_replace_callback($re, function ($m) use (&$stack, &$tail, $void) {
            if (!isset($m[4])) {
                // Comment or text-only element: opaque. Close it if the fragment ends inside it.
                if (isset($m[1])) {
                    if (!isset($m[2])) {
                        $tail = '</' . strtolower($m[1]) . '>';
                    }
                } elseif (substr($m[0], -3) !== '-->') {
                    $tail = '-->';
                }
                return '';
            }
            $name = strtolower($m[4]);
            if ($m[3] === '/') {
                // Pops the start tag and what the browser closes with it. An end tag
                // without a start tag in this fragment is left alone.
                $i = count($stack) - 1;
                while ($i >= 0 && $stack[$i] !== $name) {
                    $i--;
                }
                if ($i >= 0) {
                    array_splice($stack, $i);
                }
            } elseif (!isset($void[$name])) {
                // "/>" only closes SVG/MathML elements; <div/> stays open in a browser.
                $self_closed = substr($m[5], -1) === '/' && ($name === 'svg' || $name === 'math'
                    || in_array('svg', $stack, true) || in_array('math', $stack, true));
                if (!$self_closed) {
                    $stack[] = $name;
                }
            }
            return '';
        }, $html);
        if ($done === null) {
            return $html; // PCRE limit hit: leave the author's HTML untouched
        }
        while (($name = array_pop($stack)) !== null) {
            if (!isset($implied[$name])) {
                $tail .= '</' . $name . '>';
            }
        }
        return $html . $tail;
    }

}
?>
