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
class ModuleMailComposeEmailView extends BaseView
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
        require __DIR__ . "/tpl_moduleMailComposeEmail.php";
    }

    /**
     * Render the entry form view
     */
    protected function output_entry_form()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => 'Compose Email',
            "children" => array(
                new BaseStyleComponent("form", array(
                    "id" => 'composeEmailForm',
                    "label" => "Compose Email",
                    "url" => $this->model->get_link_url("moduleMail"),
                    "url_cancel" => $this->model->get_link_url("moduleMail"),
                    "label_cancel" => 'Cancel',
                    "url_type" => 'warning',
                    "type" => 'warning',
                    "children" => array(
                        new BaseStyleComponent("template", array(
                            "path" => __DIR__ . "/tpl_selectRecipients.php",
                            "items" => array(
                                "name" => 'recipients[]',
                                "label" => "To",
                                "id" => "recipients",
                                "users" => $this->model->get_users(),
                                "groups" => $this->model->get_groups()
                            )
                        )),
                        new BaseStyleComponent("input", array(
                            "label" => "From email",
                            "type_input" => "email",
                            "name" => "from_email",
                            "is_required" => true,
                            "placeholder" => "From email",
                        )),
                        new BaseStyleComponent("input", array(
                            "label" => "From name",
                            "type_input" => "text",
                            "name" => "from_name",
                            "is_required" => true,
                            "placeholder" => "From name",
                        )),
                        new BaseStyleComponent("input", array(
                            "label" => "Reply To",
                            "type_input" => "email",
                            "name" => "reply_to",
                            "is_required" => true,
                            "placeholder" => "reply to email",
                        )),
                        new BaseStyleComponent("template", array(
                            "path" => __DIR__ . "/tpl_datepicker.php",
                            "items" => array(
                                "name" => 'time_to_be_sent',
                                "label" => "When",
                                "id" => "time_to_be_sent"
                            )
                        )),
                        new BaseStyleComponent("input", array(
                            "label" => "Subject",
                            "type_input" => "text",
                            "name" => "subject",
                            "is_required" => true,
                            "placeholder" => "Subject",
                        )),
                        new BaseStyleComponent("textarea", array(
                            "label" => "Message",
                            "type_input" => "text",
                            "name" => "body",
                            "placeholder" => "@user_name can be used for showing the user",
                        )),
                        new BaseStyleComponent("input", array(
                            "value" => "composeEmail",
                            "name" => "mode",
                            "type_input" => "hidden",
                        ))
                    )
                ))
            )
        ));
        $form->output_content();
    }

    /**
     * Render the sidebar buttons
     */
    public function output_side_buttons()
    {
        // maoduel queue back button
        $mailQueueuButton = new BaseStyleComponent("button", array(
            "label" => "Mail Queueu",
            "url" => $this->model->get_link_url("moduleMail"),
            "type" => "secondary",
            "css" => "d-block mb-3",
        ));
        $mailQueueuButton->output_content();
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
            $local = array(
                __DIR__ . "/../js/simplemde.min.js",
                __DIR__ . "/js/moduleMailComposeEmail.js"
            );
        }
        return parent::get_js_includes($local);
    }

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/../css/simplemde.min.css");
        return parent::get_css_includes($local);
    }
}
?>
