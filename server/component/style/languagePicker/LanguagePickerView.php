<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the language picker style component.
 *
 * It offers the same choice the footer does, as a component that can be placed
 * on any page. A headless page has no footer, so without this a participant on
 * such a page has no way to change language at all - which matters most before
 * a study starts, where the language must be set once and then left alone.
 *
 * The JS is an include rather than an inline script on purpose: the page CSP
 * pins a script hash, which voids `unsafe-inline` and blocks every inline
 * <script> tag, so inline handlers never run. See BasePage::getCspRules().
 */
class LanguagePickerView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'display_style' ('buttons').
     * How the options are rendered: 'buttons' or 'select'.
     */
    private $display_style;

    /**
     * DB field 'label' (empty string).
     * Text shown above or beside the options. Empty renders no label.
     */
    private $label;

    /**
     * DB field 'redirect_at_select' (empty string).
     * Page keyword to open once a language is chosen. Empty reloads the
     * current page, which is what the footer picker does.
     */
    private $redirect_at_select;

    /**
     * DB field 'highlight_selected' (1).
     * Whether to mark the language currently in force.
     */
    private $highlight_selected;

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
        $this->display_style = $this->model->get_db_field("display_style", "buttons");
        $this->label = $this->model->get_db_field("label", "");
        $this->redirect_at_select = $this->model->get_db_field("redirect_at_select", "");
        $this->highlight_selected = $this->model->get_db_field("highlight_selected", 1);
    }

    /* Private Methods ********************************************************/

    /**
     * The url to open once a language is chosen, or an empty string to reload
     * the current page.
     *
     * @retval string
     */
    private function get_redirect_url()
    {
        $target = trim((string) $this->redirect_at_select);
        if ($target === "") {
            return "";
        }
        // A router keyword where one is registered - core pages are - and a path
        // otherwise: pages a plugin creates are not registered as named routes,
        // so get_link_url() returns an empty string for them.
        // Only a section-backed model can resolve a router keyword. Rendered
        // without one, fall through to treating the target as a path.
        if ($this->model instanceof BaseModel) {
            $url = $this->model->get_link_url($target);
            if ($url !== "") {
                return $url;
            }
        }
        if (strpos($target, "/") === 0) {
            return $target;
        }
        return BASE_PATH . "/" . ltrim($target, "/");
    }

    /**
     * The languages to offer.
     *
     * A section-backed model reads them from the database. `BaseStyleModel`,
     * which `BaseStyleComponent` builds when the style is rendered without a
     * section, has no database access, so a caller in that position passes the
     * list in as a `languages` field instead. Both arrive here as the same
     * `id` / `title` rows the template expects.
     *
     * @return array
     *  The languages, each with an `id` and a `title`.
     */
    private function get_languages()
    {
        $languages = $this->model->get_db_field("languages", array());
        if (!empty($languages)) {
            return $languages;
        }
        return $this->model instanceof BaseModel ? $this->model->get_languages() : array();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the language picker.
     */
    public function output_content()
    {
        $languages = $this->get_languages();
       // One language is not a choice; render nothing rather than a control
        // that cannot do anything, which is how the footer behaves.
        if (count($languages) < 2) {
            return;
        }
        // Before anyone has chosen, marking the session default reads as a
        // selection the visitor did not make, so a first-choice page turns it off.
        $current = $this->highlight_selected && isset($_SESSION['language']) ? $_SESSION['language'] : null;
        $style = $this->display_style === "select" ? "select" : "buttons";
        $redirect = $this->get_redirect_url();
        // Passed as locals, not read off $this: the template is the contract
        // between this view and anything else that renders the same markup.
        $css = $this->css;
        $label = $this->label;
        require __DIR__ . "/tpl_language_picker.php";
    }

    /**
     * Get css include files required for this component.
     *
     * @return array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        if (empty($local)) {
            $local = array(__DIR__ . "/css/language-picker.css");
        }
        return parent::get_css_includes($local);
    }

    /**
     * Get js include files required for this component.
     *
     * @return array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        if (empty($local)) {
            $local = array(__DIR__ . "/js/language-picker.js");
        }
        return parent::get_js_includes($local);
    }
}
?>
