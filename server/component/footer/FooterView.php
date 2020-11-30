<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the footer component.
 */
class FooterView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Private Methods ********************************************************/

    /**
     * Render a footer link.
     *
     * @param string $key
     *  The identification string of a route.
     * @param string $page_name
     *  The title of the page the link is pointing to.
     */
    private function output_footer_link($key, $page_name)
    {
        $active = ($this->model->is_link_active($key)) ? "active" : "";
        $url = $this->model->get_link_url($key);
        require __DIR__ . "/tpl_footer_link.php";
    }

    /**
     * Render all footer languages.
     */
    private function output_footer_languages()
    {
        $languages = $this->model->get_languages();
        $options = [];
        foreach ($languages as $language)
            array_push($options, array(
                "value" => $language['locale'],
                "text" => $language['title']
            ));
        if (count($options) > 1) {
            //show footer only if there are more than 1 language
            $langOptions = new BaseStyleComponent("select", array(
                //"label" => "CMS Content Language",
                "css" => "text-dark smallOverwitten",
                "value" => $_SESSION['user_language'],
                "name" => "default_language_locale",
                "items" => $options,
            ));
            $langOptions->output_content();
        }
    }

    /**
     * Render all footer links.
     */
    private function output_footer_links()
    {
        $pages = $this->model->get_pages();
        $first = true;
        foreach ($pages as $key => $page_name) {
            if (!$first) echo "|";
            $this->output_footer_link($key, $page_name);
            $first = false;
        }
    }

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
        $local = array(__DIR__ . "/footer.css");
        return parent::get_css_includes($local);
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        if (empty($local)) {
            $local = array(__DIR__ . "/footer.js");
        }
        return parent::get_js_includes($local);
    }

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_footer.php";
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
