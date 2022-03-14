<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the user update component.
 */
class UserUpdateView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * An array of user properties (see UserModel::fetch_user).
     */
    private $selected_user;

    /**
     * The update mode of the user. This must be one of the following values:
     *  'block':       Block a user.
     *  'unblock':     Unblock a user.
     *  'add_group':   Add a group to the user.
     *  'rm_group':    Remove a group from a user.
     */
    private $mode;

    /**
     * The state of the user, which is either active, inactive, or blocked.
     */
    private $user_status;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user update component.
     * @param object $controller
     *  The controller instance of the user update component.
     * @param string $mode
     *  The update mode of the user. This must be one of the following values:
     *   'block':       Block a user.
     *   'unblock':     Unblock a user.
     *   'add_group':   Add a group to the user.
     *   'rm_group':    Remove a group from a user.
     */
    public function __construct($model, $controller, $mode)
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
        $this->selected_user = $this->model->get_selected_user();
        $this->user_status = $this->selected_user['status'];
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alert message.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
    }

    /**
     * Render the form to add new groups to a auser.
     */
    private function output_form_add_groups()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Adding Groups",
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => "Add Groups",
                    "url" => $this->model->get_link_url("userUpdate",
                        array(
                            "uid" => $this->selected_user['id'],
                            "mode" => "add_group",
                        )
                    ),
                    "url_cancel" => $this->model->get_link_url("userSelect",
                        array("uid" => $this->selected_user['id'])),
                    "children" => array(
                        new BaseStyleComponent("select", array(
                            "name" => "groups[]",
                            "is_multiple" => true,
                            "live_search" => true,
                            "items" => $this->model->get_new_group_options(
                                $this->selected_user['id']),
                            "css" => "mb-3",
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the form to send activation email to a user.
     */
    private function output_form_activation_email()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Send Activation Email",
            "type" => "info",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "The user will recive an activation email",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Send Email",
                    "url" => $this->model->get_link_url("userUpdate",
                        array(
                            "uid" => $this->selected_user['id'],
                            "mode" => "activation_email",
                        )
                    ),
                    "type" => "info",
                    "url_cancel" => $this->model->get_link_url("userSelect",
                        array("uid" => $this->selected_user['id'])),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "activation_email",
                            "value" => $this->selected_user['email']
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the form to block a user.
     */
    private function output_form_block()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Block User",
            "type" => "warning",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "Blocking a user will prevent this user from logging in to the platform.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Block User",
                    "url" => $this->model->get_link_url("userUpdate",
                        array(
                            "uid" => $this->selected_user['id'],
                            "mode" => "block",
                        )
                    ),
                    "type" => "warning",
                    "url_cancel" => $this->model->get_link_url("userSelect",
                        array("uid" => $this->selected_user['id'])),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "block",
                            "value" => 1,
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the clean user data form.
     */
    private function output_form_clean()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Clean User Data",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the email address of the user.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Clean User Data",
                    "url" => $this->model->get_link_url("userUpdate",
                        array("uid" => $this->selected_user['id'],
                            "mode" => "clean")),
                    "type" => "danger",
                    "url_cancel" => $this->model->get_link_url("userSelect",
                        array("uid" => $this->selected_user['id'])),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "email",
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter Email Address",
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the from to remove a group from a user.
     */
    private function output_form_rm_group()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Remove Group",
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => "Remove Group",
                    "url" => $this->model->get_link_url("userUpdate",
                        array(
                            "uid" => $this->selected_user['id'],
                            "mode" => "rm_group",
                            "did" => $this->model->get_did(),
                        )
                    ),
                    "url_cancel" => $this->model->get_link_url("userSelect",
                        array("uid" => $this->selected_user['id'])),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "rm_group",
                            "value" => $this->model->get_did(),
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the form to unblock a user.
     */
    private function output_form_unblock()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Unblock User",
            "type" => "warning",
            "children" => array(
                new BaseStyleComponent("markdown", array(
                    "text_md" => "<p>Unblocking a user will restore the user status to <code>" . $this->user_status . "</code>.</p>",
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Unblock User",
                    "url" => $this->model->get_link_url("userUpdate",
                        array(
                            "uid" => $this->selected_user['id'],
                            "mode" => "unblock",
                        )
                    ),
                    "type" => "warning",
                    "url_cancel" => $this->model->get_link_url("userSelect",
                        array("uid" => $this->selected_user['id'])),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "unblock",
                            "value" => 1,
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the specific success message.
     *
     * @param string $type
     *  The type of success message to render.
     */
    private function output_success($type)
    {
        if($type === "block")
            require __DIR__ . "/tpl_success_block.php";
        else if($type === "unblock")
            require __DIR__ . "/tpl_success_unblock.php";
         else if($type === "activation_email")
            require __DIR__ . "/tpl_success_activation_email.php";
        else if($type === "clean")
            require __DIR__ . "/tpl_success_clean.php";
        else if($type === "add_group")
            require __DIR__ . "/tpl_success_add_group.php";
        else if($type === "rm_group")
            require __DIR__ . "/tpl_success_rm_group.php";
        else
            echo "<p>Success-type '$type' does not exist.</p>";
    }

    /**
     * Render a list of user groups.
     */
    private function output_user_groups()
    {
        $groups = new BaseStyleComponent("sortableList", array(
            "is_editable" => false,
            "items" => $this->model->get_selected_user_groups(),
            "css" => "mb-3",
        ));
        $groups->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        $url_user = $this->model->get_link_url("userSelect",
            array("uid" => $this->selected_user['id']));
        $url_users = $this->model->get_link_url("userSelect");
        if($this->mode == "activation_email")
        {
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success.php";
            else
                require __DIR__ . "/tpl_activation_email.php";
        }
        if($this->mode == "block")
        {
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success.php";
            else
                require __DIR__ . "/tpl_block.php";
        }
        else if($this->mode == "unblock")
        {
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success.php";
            else
                require __DIR__ . "/tpl_unblock.php";
        }
        else if($this->mode == "clean")
        {
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success.php";
            else
                require __DIR__ . "/tpl_clean.php";
        }
        else if($this->mode == "add_group")
        {
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success.php";
            else
                require __DIR__ . "/tpl_add_group.php";
        }
        else if($this->mode == "rm_group")
        {
            $group = $this->model->get_rm_group_name();
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success.php";
            else
                require __DIR__ . "/tpl_rm_group.php";
        }
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
