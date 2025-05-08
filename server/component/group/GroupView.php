<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the group select and update component.
 */
class GroupView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * An array of group properties (see UserModel::fetch_group).
     */
    private $selected_group;

    /**
     * The mode of the current operation. This is eithert 'update' or 'select'.
     */
    private $mode;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user component.
     * @param object $controller
     *  The controller instance of the group component.
     * @param string $mode
     *  See GroupView::mode
     */
    public function __construct($model, $controller = null, $mode = "select")
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
        $this->selected_group = $this->model->fetch_group($this->model->get_gid());
    }

    /* Private Methods ********************************************************/

    /**
     * Render the alert message.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_controller_alerts_success();
    }

    /**
     * Render the button to create a new group.
     */
    private function output_button() {}

    /**
     * Render the group delete card.
     */
    private function output_group_delete()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Delete Group",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "Deleting a group will remove all data associated to this group. This cannot be undone.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("button", array(
                    "label" => "Delete Group",
                    "url" => $this->model->get_link_url(
                        "groupDelete",
                        array("gid" => $this->selected_group['id'])
                    ),
                    "type" => "danger",
                )),
            )
        ));
        $card->output_content();
    }

    /**
     * Render the simplified ACL of groups.
     */
    private function output_group_manipulation()
    {
        if ($this->mode == "update")
            $this->output_group_simple_acl_form();
        else
            $this->output_group_simple_acl();
        if ($this->model->can_delete_group())
            $this->output_group_delete();
    }

    /**
     * Render the ACL table.
     */
    private function output_group_simple_acl()
    {
        $url_edit = "";
        if ($this->model->can_modify_group_acl())
            $url_edit = $this->model->get_link_url(
                "groupUpdate",
                array('gid' => $this->selected_group['id'])
            );
        $table = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "url_edit" => $url_edit,
            "title" => "Group Access Rights",
            "children" => array(new BaseStyleComponent("acl", array(
                "title" => "Function",
                "items" => $this->model->get_simple_acl_selected_group()
            )))
        ));
        $table->output_content();
    }

    /**
     * Render the ACL from.
     */
    private function output_group_simple_acl_form()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "type" => "warning",
            "title" => "Modify Group Access Rights",
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => "Update Group",
                    "url" => $this->model->get_link_url(
                        "groupUpdate",
                        array(
                            "gid" => $this->selected_group['id'],
                        )
                    ),
                    "type" => "warning",
                    "url_cancel" => $this->model->get_link_url(
                        "groupSelect",
                        array("gid" => $this->selected_group['id'])
                    ),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "update_acl",
                            "value" => 1,
                        )),
                        new BaseStyleComponent("acl", array(
                            "title" => "Function",
                            "is_editable" => true,
                            "items" => $this->model->get_simple_acl_selected_group(),
                            "items_granted" => $this->model->get_simple_acl_current_user(),
                        ))
                    ),
                ))

            )
        ));
        $form->output_content();
    }

    /**
     * Render the list of users.
     */
    private function output_groups()
    {
        if ($this->model->can_create_new_group()) {
            $button = new BaseStyleComponent("button", array(
                "label" => "Create New DB Role",
                "url" => $this->model->get_link_url("groupInsert", array("type" => groupTypes_db_role)),
                "type" => "secondary",
                "css" => "d-block mb-3",
            ));
            $button->output_content();
        }

        if ($this->model->can_create_new_group()) {
            $button = new BaseStyleComponent("button", array(
                "label" => "Create New Group",
                "url" => $this->model->get_link_url("groupInsert", array("type" => groupTypes_group)),
                "type" => "secondary",
                "css" => "d-block mb-3",
            ));
            $button->output_content();
        }

        $db_roles = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "DB Roles",
            "children" => array(new BaseStyleComponent("nestedList", array(
                "items" => $this->model->get_groups(groupTypes_db_role),
                "id_prefix" => "db_roles",
                "is_collapsible" => false,
                "id_active" => $this->model->get_gid(),
                "search_text" => 'Search'
            )))
        ));
        $db_roles->output_content();

        $groups = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Groups",
            "children" => array(new BaseStyleComponent("nestedList", array(
                "items" => $this->model->get_groups(groupTypes_group),
                "id_prefix" => "groups",
                "is_collapsible" => false,
                "id_active" => $this->model->get_gid(),
                "search_text" => 'Search'
            )))
        ));
        $groups->output_content();
    }

    /**
     * Render the user description or the intro text.
     */
    private function output_main_content()
    {
        if ($this->selected_group != null)
            require __DIR__ . "/tpl_group.php";
        else
            require __DIR__ . "/tpl_groups.php";
    }

    /**
     * Render the ACL list.
     */
    private function output_group_acl()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => false,
            "is_collapsible" => true,
            "title" => "ACL",
            "children" => array(new BaseStyleComponent("acl", array(
                "title" => "Page",
                "items" => $this->model->get_acl_selected_group()
            )))
        ));
        $card->output_content();
    }

    /**
     * Render preferences wrapper.
     */
    private function output_group()
    {
        if ($this->model->get_services()->get_router()->current_route['name'] == "groupUpdate") {
            $this->output_group_form_edit();
        } else {
            $this->output_group_form_view();
        }
    }

    private function output_group_form_view()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => ($this->selected_group['group_type'] == groupTypes_db_role ? 'DB Role' : 'Group') . " &nbsp;  <code> " . $this->selected_group['name'] . "</code>",
            "url_edit" => $this->model->get_link_url("groupUpdate", array("gid" => $this->selected_group['id'])),
            "children" => array(
                new BaseStyleComponent("descriptionItem", array(
                    "title" => ($this->selected_group['group_type'] == groupTypes_db_role ? 'DB Role' : 'Group') . " Name",
                    "help" => "The name of the group",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->selected_group['name']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => ($this->selected_group['group_type'] == groupTypes_db_role ? 'DB Role' : 'Group') . " Description",
                    "help" => "The description of the group",
                    "locale" => "",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->selected_group['desc']
                    ))),
                )),
                new BaseStyleComponent("descriptionItem", array(
                    "title" => "2FA Required",
                    "locale" => "",
                    "help" => "If enabled users should login with 2FA enabled.",
                    "children" => array(new BaseStyleComponent("rawText", array(
                        "text" => $this->selected_group['requires_2fa'] == 1 ? "true" : "false"
                    ))),
                ))
            )
        ));
        $card->output_content();
    }

    private function output_group_form_edit()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "type" => "warning",
            "title" => "Update " . ($this->selected_group['group_type'] == groupTypes_db_role ? 'DB Role' : 'Group') . " &nbsp;  <code> " . $this->selected_group['name'] . "</code>",
            "url_edit" => $this->model->get_link_url("groupUpdate", array("gid" => $this->selected_group['id'])),
            "children" => array(new BaseStyleComponent("form", array(
                "label" => "Update",
                "url" => $this->model->get_link_url("groupUpdate", array("gid" => $this->selected_group['id'])),
                "url_cancel" => $this->model->get_link_url("groupSelect", array("gid" => $this->selected_group['id'])),
                "type" => "warning",
                "children" => array(
                    new BaseStyleComponent("descriptionItem", array(
                        "title" => ($this->selected_group['group_type'] == groupTypes_db_role ? 'DB Role' : 'Group') . " Name",
                        "help" => "The name of the group",
                        "locale" => "",
                        "children" => array(new BaseStyleComponent("rawText", array(
                            "text" => $this->selected_group['name']
                        ))),
                    )),
                    new BaseStyleComponent("input", array(
                        "type_input" => "hidden",
                        "name" => "update_group",
                        "value" => 1,
                    )),
                    new BaseStyleComponent("textarea", array(
                        "help" => "The description of the group",
                        "label" => ($this->selected_group['group_type'] == groupTypes_db_role ? 'DB Role' : 'Group') . " Description",
                        "type_input" => "text",
                        "name" => "desc",
                        "required" => true,
                        "value" => $this->selected_group['desc'],
                        "placeholder" => "Enter description",
                    )),
                    new BaseStyleComponent("descriptionItem", array(
                        "title" => "2FA Required",
                        "locale" => "",
                        "help" => "If enabled users should login with 2FA enabled.",
                        "children" => array(new BaseStyleComponent("input", array(
                            "type_input" => "checkbox",
                            "checkbox_value" => "1",
                            "name" => "requires_2fa",
                            "value" => isset($this->selected_group['requires_2fa']) ? $this->selected_group['requires_2fa'] : 0,

                        )),),
                    ))
                ),
                "css" => ""
            ))),

        ));
        $card->output_content();
    }

    /**
     * Render the ACL list with edit mode.
     */
    private function output_group_acl_custom()
    {
        if ($this->mode == "updateCustom")
            $this->output_group_acl_custom_form();
        else
            $this->output_group_acl_custom_table();
        if ($this->model->can_delete_group())
            $this->output_group_delete();
    }

    /**
     * Render the ACL form.
     */
    private function output_group_acl_custom_form()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "type" => "warning",
            "title" => "Modify ACL for " . ($this->selected_group['group_type'] == groupTypes_db_role ? 'DB Role' : 'Group') . " &nbsp; <code>" .  $this->selected_group['name'] . "</code>",
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => "Update Group",
                    "url" => $this->model->get_link_url(
                        "groupUpdateCustom",
                        array(
                            "gid" => $this->selected_group['id'],
                        )
                    ),
                    "type" => "warning",
                    "url_cancel" => $this->model->get_link_url(
                        "groupSelect",
                        array("gid" => $this->selected_group['id'])
                    ),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "update_custom_acl",
                            "value" => 1,
                        )),
                        new BaseStyleComponent("acl", array(
                            "title" => "Function",
                            "is_editable" => true,
                            "items" => $this->model->get_acl_selected_group(),
                            "items_granted" => $this->model->get_user_acl($_SESSION['id_user'], false)
                        ))
                    ),
                ))

            )
        ));
        $card->output_content();
    }

    /**
     * Render the ACL table.
     */
    private function output_group_acl_custom_table()
    {
        $url_edit = "";
        if ($this->model->can_modify_group_acl())
            $url_edit = $this->model->get_link_url(
                "groupUpdateCustom",
                array('gid' => $this->selected_group['id'])
            );
        $table = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "url_edit" => $url_edit,
            "title" => "ACL for " . ($this->selected_group['group_type'] == groupTypes_db_role ? 'DB Role' : 'Group') . " &nbsp; <code>" .  $this->selected_group['name'] . "</code>",
            "children" => array(new BaseStyleComponent("acl", array(
                "title" => "Function",
                "items" => $this->model->get_acl_selected_group()
            )))
        ));
        $table->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_main.php";
    }

    public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
