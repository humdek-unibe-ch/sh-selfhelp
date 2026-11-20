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
        $cmsPreferencesChildren = array(
            new BaseStyleComponent("descriptionItem", array(
                "title" => "CMS Content Language",
                "help" => "The default content language",
                "locale" => "",
                "children" => array(new BaseStyleComponent("select", array(
                    "value" => $this->model->get_cmsPreferences()['default_language_id'],
                    "name" => "default_language_id",
                    "items" => $options,
                    "css" => "mb-3",
                )),),
            )),
            new BaseStyleComponent("descriptionItem", array(
                "title" => "Callback API Key",
                "help" => "Callback API key which is used for sending requests back to Selfhelp",
                "locale" => "",
                "children" => array(new BaseStyleComponent("input", array(
                    "value" => $this->model->get_cmsPreferences()['callback_api_key'],
                    "name" => "callback_api_key",
                    "css" => "mb-3",
                )),),
            )),
            new BaseStyleComponent("descriptionItem", array(
                "title" => "Firebase config",
                "help" => "The firebase JSON [from](https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk)",
                "locale" => "",
                "children" => array(new BaseStyleComponent("textarea", array(
                    "value" => $this->model->get_cmsPreferences()['firebase_config'],
                    "type_input" => "json",
                    "name" => "firebase_config",
                    "css" => "mb-3",
                )),),
            )),
            new BaseStyleComponent("descriptionItem", array(
                "title" => "Anonymous Users",
                "locale" => "",
                "help" => "If enabled all the users can login with their  <code>User name</code> and <code>password</code>. All new users will not require an email for registration instead the users should answer 2 security questions.",
                "children" => array(new BaseStyleComponent("input", array(
                    "type_input" => "checkbox",
                    "checkbox_value" => "1",
                    "name" => "anonymous_users",
                    "value" => isset($this->model->get_cmsPreferences()['anonymous_users']) ? $this->model->get_cmsPreferences()['anonymous_users'] : 0,

                )),),
            ))
        );
        foreach ($this->get_landing_page_settings() as $name => $setting) {
            $cmsPreferencesChildren[] = new BaseStyleComponent("descriptionItem", array(
                "title" => $setting['title'],
                "help" => $setting['help'],
                "locale" => "",
                "children" => array(new BaseStyleComponent("select", array(
                    "value" => intval($this->model->get_cmsPreferences()[$name] ?? 0),
                    "name" => $name,
                    "items" => $this->model->get_landing_page_options($name),
                    "live_search" => 1,
                    "css" => "mb-3",
                )),),
            ));
        }
        $cmsPreferencesChildren[] = new BaseStyleComponent("descriptionItem", array(
            "title" => "Return to Last Page after Login",
            "locale" => "",
            "help" => "If enabled a user returns to the page visited last before the logout. If disabled every login opens the <code>Page after Login</code>. A link which led to the login page is followed in both cases.",
            "children" => array(new BaseStyleComponent("input", array(
                "type_input" => "checkbox",
                "checkbox_value" => "1",
                "name" => "login_return_last_page",
                "value" => $this->model->get_cmsPreferences()['login_return_last_page'] ?? 1,
            )),),
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
                "children" => $cmsPreferencesChildren,
                "css" => "cms-preferences-form"
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
        $children = array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "CMS Content Language",
                    "help" => "The default content language",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->model->get_cmsPreferences()['default_language']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Callback API Key",
                    "help" => "Callback API key which is used for sending requests back to Selfhelp",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->model->get_cmsPreferences()['callback_api_key']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Firebase config",
                    "help" => "The firebase JSON [from](https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk)",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->model->get_cmsPreferences()['firebase_config'],
                        "css" => "firebaseConfig"
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "Anonymous Users",
                    "locale" => "",
                    "help" => "If enabled all the users can login with their  <code>User name</code> and <code>password</code>. All new users will not require an email for registration instead the users should answer 2 security questions.",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->model->get_cmsPreferences()['anonymous_users'] == 1 ? "true" : "false"
                    ))),
                ))
        );
        foreach ($this->get_landing_page_settings() as $setting) {
            $children[] = new BaseStyleComponent("descriptionItem", array(
                "title" => $setting['title'],
                "help" => $setting['help'],
                "locale" => "",
                "children" => array(new BaseStyleComponent("rawText", array(
                    "text" => $this->model->get_cmsPreferences()[$setting['keyword']] ?? "Default (" . $setting['default'] . ")"
                ))),
            ));
        }
        $children[] = new BaseStyleComponent("descriptionItem", array(
            "title" => "Return to Last Page after Login",
            "locale" => "",
            "help" => "If enabled a user returns to the page visited last before the logout. If disabled every login opens the <code>Page after Login</code>. A link which led to the login page is followed in both cases.",
            "children" => array(new BaseStyleComponent("rawText", array(
                "text" => ($this->model->get_cmsPreferences()['login_return_last_page'] ?? 1) == 1 ? "true" : "false"
            ))),
        ));
        $cmsPreferences = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "CMS Preferences",
            "children" => $children,
            "url_edit" => $this->model->get_link_url("cmsPreferencesUpdate")
        ));
        $cmsPreferences->output_content();
    }

    /**
     * The landing page settings shown in the preferences.
     *
     * @retval array
     *  The settings keyed by the column name, each with 'title', 'help',
     *  'keyword' (the view column holding the keyword of the chosen page) and
     *  'default' (what an empty setting means).
     */
    private function get_landing_page_settings()
    {
        return array(
            "id_pages_landing_user" => array(
                "title" => "Landing Page (logged in)",
                "help" => "The page a logged-in user gets when the website is called without a page (the site root <code>/</code>) or the logo in the header is clicked. A user without access to it gets the <code>Landing Page (guest)</code>, or <code>" . SH_HOME . "</code> if that is <code>" . SH_LOGIN . "</code> or empty.",
                "keyword" => "landing_user_page_keyword",
                "default" => SH_HOME
            ),
            "id_pages_landing_guest" => array(
                "title" => "Landing Page (guest)",
                "help" => "The page a guest gets at the site root <code>/</code> or from the logo in the header. Only open pages and <code>" . SH_LOGIN . "</code> can be chosen.",
                "keyword" => "landing_guest_page_keyword",
                "default" => SH_HOME
            ),
            "id_pages_after_login" => array(
                "title" => "Page after Login",
                "help" => "The page a user lands on after the login (see <code>Return to Last Page after Login</code>). Empty: the <code>Landing Page (logged in)</code>.",
                "keyword" => "after_login_page_keyword",
                "default" => "Landing Page (logged in)"
            )
        );
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_cmsPreferences.php";
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }

    /**
     * Render the alert message.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @return array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(
            __DIR__ . "/css/cmsPreferences.css"
        );
        return parent::get_css_includes($local);
    }
}
?>
