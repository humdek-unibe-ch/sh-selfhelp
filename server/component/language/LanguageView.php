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
class LanguageView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
    }

    /* Private Methods ********************************************************/


    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $language = $this->controller->get_new_language();
            $url = $this->model->get_link_url("cmsPreferences");     
            $mode = $this->controller->get_mode();       
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $cancel_url = $this->model->get_link_url("cmsPreferences");
            if($this->model->get_language_id() > 1){
                $action_url = $this->model->get_link_url("language", array(
                    "lid" => $this->model->get_language_id()
                ));
                $selectedLanguage = $this->model->get_selected_language();
                $this->mode = 'update';
                require __DIR__ . "/tpl_languege.php";
            }else{
                $action_url = $this->model->get_link_url("language");                
                $this->mode  = 'insert';
                require __DIR__ . "/tpl_insertLanguege.php";
            }
        }
    }

    private function output_delete_form()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => false,
            "is_collapsible" => true,
            "title" => "Delete Language",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the locale of the language.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Delete Language",
                    "url" => $this->model->get_link_url("language",
                        array("lid" => $this->model->get_language_id())),                    
                    "type" => "danger",
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "deleteLocale",
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter language locale",
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the alert message.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();        
    }
}
?>
