<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class CmsPreferencesView extends BaseView
{
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
    }

    /* Private Methods ********************************************************/

    /**
     * Render the asset list.
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css' or 'asset').
     */
    private function output($mode)
    {
        echo $mode;
    }

    /**
     * Render the button to create a new language.
     */
    private function output_button()
    {
        if($this->model->can_create_new_language())
        {
            $button = new BaseStyleComponent("button", array(
                "label" => "Create New Language",
                "url" => $this->model->get_link_url("language"),
                "type" => "secondary",
                "css" => "d-block mb-3",
            ));
            $button->output_content();
        }
    }

    /**
     * Render the list of languages.
     */
    private function output_languages()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Languages",
            "children" => array(new BaseStyleComponent("nestedList", array(
                "items" => $this->model->get_languages(),
                "id_prefix" => "languages",
                "is_collapsible" => false
            )))
        ));
        $card->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_cmsPreferences.php";
    }
}
?>
