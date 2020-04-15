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

    /* Private Properties *****************************************************/

    /**
     *  The router instance is used to generate valid links.
     */
    private $mode;


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller, $mode)
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
    }

    /* Private Methods ********************************************************/

    /**
     * Render the button to create a new language.
     */
    private function output_button()
    {
        if ($this->model->can_create_new_language()) {
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

    /**
     * Render preferences wrapper.
     */
    private function output_cms_preferences_form()
    {
        if ($this->mode == "edit") {
            $this->output_cms_preferences_form_edit();
        } else {
            $this->output_cms_preferences_form_view();
        }
    }

    /**
     * Render preferences in edit mdoe.
     */
    private function output_cms_preferences_form_edit()
    {
        $languages = $this->model->get_languages();
        $options = [];
        foreach ($languages as $language)
            array_push($options, array(
                "value" => $language['id'],
                "text" => $language['title']
            ));
        $cmsPreferences = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Edit CMS Preferences",
            "type" => "warning",
            "children" => array(new BaseStyleComponent("form", array(
                "url" => $this->model->get_link_url("cmsPreferences"),
                "url_cancel" => $this->model->get_link_url("cmsPreferences"),
                "type" => "warning",
                "children" => array(
                    new BaseStyleComponent("select", array(
                        "label" => "CMS Content Language",
                        "value" => $this->model->get_cmsPreferences()['default_language_id'],
                        "name" => "default_language_id",
                        "items" => $options,
                    )),
                     new BaseStyleComponent("input", array(
                        "label" => "Callback API Key",
                        "value" => $this->model->get_cmsPreferences()['callback_api_key'],
                        "name" => "callback_api_key"
                    ))
                )
            ))),
            "url_edit" => $this->model->get_link_url("cmsPreferencesUpdate")
        ));
        $cmsPreferences->output_content();
    }

    /**
     * Render preferences in view mdoe.
     */
    private function output_cms_preferences_form_view()
    {
        $languages = $this->model->get_languages();
        $options = [];
        foreach ($languages as $language)
            array_push($options, array(
                "value" => $language['id'],
                "text" => $language['title']
            ));
        $cmsPreferences = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "CMS Preferences",
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "CMS Content Language",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->model->get_cmsPreferences()['default_language']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Callback API Key",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->model->get_cmsPreferences()['callback_api_key']
                    ))),
                ))
            ),
            "url_edit" => $this->model->get_link_url("cmsPreferencesUpdate")
        ));
        $cmsPreferences->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_cmsPreferences.php";
    }

    /**
     * Render the alert message.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();        
        $this->output_controller_alerts_success();    
    }
}
?>
