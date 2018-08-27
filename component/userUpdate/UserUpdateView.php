<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the user update component.
 */
class UserUpdateView extends BaseView
{
    /* Private Properties *****************************************************/

    private $selected_user;
    private $mode;
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
        $this->add_local_component("alert-fail-block",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to block the user.",
                )))
            ))
        );
        $this->add_local_component("alert-fail-unblock",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to unblock the user.",
                )))
            ))
        );
        $this->add_local_component("alert-fail-add-group",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to add groups to the user.",
                )))
            ))
        );
        $this->add_local_component("alert-fail-rm-group",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to remove the group from the user.",
                )))
            ))
        );
        $this->add_local_component("form_block",
            new BaseStyleComponent("card", array(
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
                        "cancel" => true,
                        "cancel_url" => $this->model->get_link_url("user",
                            array("uid" => $this->selected_user['id'])),
                        "children" => array(
                            new BaseStyleComponent("input", array(
                                "type" => "hidden",
                                "name" => "block",
                                "value" => 1,
                            )),
                        )
                    )),
                )
            ))
        );
        $this->user_status = $this->selected_user['active'] ? "active" : "inactive";
        $this->add_local_component("form_unblock",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Unblock User",
                "type" => "warning",
                "children" => array(
                    new BaseStyleComponent("markdown", array(
                        "text_markdown" => "Unblocking a user will restore the user status to <code>" . $this->user_status . "</code>.",
                        "is_paragraph" => true,
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
                        "cancel" => true,
                        "cancel_url" => $this->model->get_link_url("user",
                            array("uid" => $this->selected_user['id'])),
                        "children" => array(
                            new BaseStyleComponent("input", array(
                                "type" => "hidden",
                                "name" => "unblock",
                                "value" => 1,
                            )),
                        )
                    )),
                )
            ))
        );
        $this->add_local_component("form_add_groups",
            new BaseStyleComponent("card", array(
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
                        "cancel" => true,
                        "cancel_url" => $this->model->get_link_url("user",
                            array("uid" => $this->selected_user['id'])),
                        "children" => array(
                            new BaseStyleComponent("select", array(
                                "name" => "groups[]",
                                "is_multiple" => true,
                                "items" => $this->model->get_new_group_options(
                                    $this->selected_user['id']),
                                "css" => "mb-3",
                            )),
                        )
                    )),
                )
            ))
        );
        $this->add_local_component("form_rm_group",
            new BaseStyleComponent("card", array(
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
                        "cancel" => true,
                        "cancel_url" => $this->model->get_link_url("user",
                            array("uid" => $this->selected_user['id'])),
                        "children" => array(
                            new BaseStyleComponent("input", array(
                                "type" => "hidden",
                                "name" => "rm_group",
                                "value" => $this->model->get_did(),
                            )),
                        )
                    )),
                )
            ))
        );
        $this->add_local_component("user_groups",
            new BaseStyleComponent("sortableList", array(
                "edit" => false,
                "items" => $this->model->get_selected_user_groups(),
                "css" => "mb-3",
            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alert message.
     */
    private function output_alert()
    {
        if($this->controller->has_failed())
        {
            if($this->mode == "block")
                $this->output_local_component("alert-fail-block");
            else if($this->mode == "unblock")
                $this->output_local_component("alert-fail-unblock");
            else if($this->mode == "add_group")
                $this->output_local_component("alert-fail-add-group");
            else if($this->mode == "rm_group")
                $this->output_local_component("alert-fail-rm-group");
        }
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        $url = $this->model->get_link_url("user",
            array("uid" => $this->selected_user['id']));
        if($this->mode == "block")
        {
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success_block.php";
            else
                require __DIR__ . "/tpl_block.php";
        }
        else if($this->mode == "unblock")
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success_unblock.php";
            else
                require __DIR__ . "/tpl_unblock.php";
        else if($this->mode == "add_group")
        {
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success_add_group.php";
            else
                require __DIR__ . "/tpl_add_group.php";
        }
        else if($this->mode == "rm_group")
        {
            $group = $this->model->get_rm_group_name();
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success_rm_group.php";
            else
                require __DIR__ . "/tpl_rm_group.php";
        }
    }
}
?>
