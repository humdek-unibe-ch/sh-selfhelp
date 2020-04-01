<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the chat admin update component.
 */
class ChatAdminUpdateView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * The update mode of the chat room. This must be one of the following
     * values:
     *  - 'add_user':   Add a user to the chat room
     *  - 'rm_user':    Remove a user from a chat room
     */
    private $mode;

    /**
     * The user id to be removed
     */
    private $did;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user update component.
     * @param object $controller
     *  The controller instance of the user update component.
     * @param string $mode
     *  See ChatAdminUpdateView::mode
     * @param int $did
     *  The id of the user to be removed from the active chat room
     */
    public function __construct($model, $controller, $mode, $did=null)
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
        $this->did = $did;
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
     * Render the autocomplete text field
     */
    private function output_autocomplete()
    {
        $ac = new BaseStyleComponent('autocomplete', array(
            "placeholder" => "Search User Email",
            "name" => "user_search",
            "name_value_field" => "add_user",
            "is_required" => true,
            "value" => "",
            "callback_class" => "AjaxSearch",
            "callback_method" => "search_user_chat"
        ));
        $ac->output_content();
    }

    /**
     * Render the form to add a new user to a chat room.
     */
    private function output_form_add_users()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Adding User",
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => "Add User",
                    "url" => $this->model->get_link_url("chatAdminUpdate",
                        array(
                            "rid" => $this->model->get_active_room(),
                            "mode" => "add_user",
                        )
                    ),
                    "url_cancel" => $this->model->get_link_url("chatAdminSelect",
                        array("rid" => $this->model->get_active_room())),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "user_search",
                            "placeholder" => "Search User Email",
                        )),
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "add_user",
                        )),
                        new BaseStyleComponent("div", array('css' => 'search-target mb-3')),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render the from to remove a group from a user.
     */
    private function output_form_rm_user()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Remove User",
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => "Remove User",
                    "url" => $this->model->get_link_url("chatAdminUpdate",
                        array(
                            "rid" => $this->model->get_active_room(),
                            "mode" => "rm_user",
                            "did" => $this->did,
                        )
                    ),
                    "url_cancel" => $this->model->get_link_url("chatAdminSelect",
                        array("rid" => $this->model->get_active_room())),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "rm_user",
                            "value" => $this->did,
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /**
     * Render a list of user groups.
     */
    private function output_user_search()
    {
    }

    /**
     * Render a list of chat room users.
     */
    private function output_room_users()
    {
        $groups = new BaseStyleComponent("sortableList", array(
            "is_editable" => false,
            "items" => $this->model->get_active_room_users(),
            "css" => "mb-3",
        ));
        $groups->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(__DIR__ . "/search.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        $url = $this->model->get_link_url("chatAdminSelect",
            array("rid" => $this->model->get_active_room()));
        $room = $this->model->get_active_room_name();
        if($this->mode == "add_user")
        {
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success_add_user.php";
            else
            {
                $url = $this->model->get_link_url("chatAdminUpdate",
                    array(
                        "rid" => $this->model->get_active_room(),
                        "mode" => "add_user",
                    )
                );
                $url_cancel = $this->model->get_link_url("chatAdminSelect",
                        array("rid" => $this->model->get_active_room()));
                require __DIR__ . "/tpl_add_user.php";
            }
        }
        else if($this->mode == "rm_user")
        {
            $user = $this->model->get_user_email($this->did);
            if($this->controller->has_succeeded())
                require __DIR__ . "/tpl_success_rm_user.php";
            else
                require __DIR__ . "/tpl_rm_user.php";
        }
    }
}
?>
