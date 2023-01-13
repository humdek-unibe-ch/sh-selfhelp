<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the user component.
 */
class UserSelectView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * An array of user properties (see UserModel::fetch_user).
     */
    private $selected_user;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->selected_user = $this->model->get_selected_user();
    }

    /* Private Methods ********************************************************/

    /**
     * Render the button to create a new user.
     */
    private function output_button()
    {
        if($this->selected_user !== null)
        {
            $button_table = new BaseStyleComponent("button", array(
                "label" => "Users",
                "url" => $this->model->get_link_url("userSelect"),
                "type" => "primary",
                "css" => "d-block mb-3",
            ));
            $button_table->output_content();
        }
        if($this->model->can_create_new_user())
        {
            $button_new = new BaseStyleComponent("button", array(
                "label" => "Create New User",
                "url" => $this->model->get_link_url("userInsert"),
                "type" => "secondary",
                "css" => "d-block mb-3",
            ));
            $button_new->output_content();
            $button_codes = new BaseStyleComponent("button", array(
                "label" => "Generate Validation Codes",
                "url" => $this->model->get_link_url("userGenCode"),
                "type" => "secondary",
                "css" => "d-block mb-3",
            ));
            $button_codes->output_content();
        }
    }

    /**
     * Render the title of a user attribute (including a popover description).
     *
     * @param string $key
     *  A key to identify which title to render.
     */
    private function output_title($key)
    {
        if($key === "id")
        {
            $title = "#";
            $content = "Unique id of the user (used internally).";
        }
        else if($key === "email")
        {
            $title = "Email";
            $content = "The email address of the user. Emails must be unique.";
        }
        else if($key === "status")
        {
            $title = "Status";
            $content = "The status of the user.";
        }
        else if($key === "code")
        {
            $title = "User Code";
            $content = "The validation code associated to the user.";
        }
        else if($key === "user_name")
        {
            $title = "User Name";
            $content = "Selected user name by the user";
        }
        else if($key === "login")
        {
            $title = "Last Login";
            $content = "The date of the last login.";
        }
        else if($key === "activity")
        {
            $title = "Activity";
            $content = "A user activity metric: The number of accesses of the user to experimenter pages (including repeted access to the same page).";
        }
        else if($key === "progress")
        {
            $title = "Progress";
            $content = "A user progress metric: The percentage of vistited experimenter pages and navigation sections";
        }
        else if($key === "groups")
        {
            $title = "Groups";
            $content = "The groups in which the user is assigned";
        }
        else
            $content = $title = "bad key";
        require __DIR__ . "/tpl_user_attr_title.php";
    }

    /**
     * Render the card to block a user.
     */
    private function output_user_blocking()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Block User",
            "type" => "warning",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "Blocking a user will set the user state to blocked. This prevents a user from logging in to the platform.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Block User",
                    "url" => $this->model->get_link_url("userUpdate",
                        array("uid" => $this->selected_user['id'],
                            "mode" => "block")),
                    "type" => "warning",
                )),
            )
        ));
        $card->output_content();
    }

    /**
     * Render the card to clean user data.
     */
    private function output_user_clean()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Clean User Data",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "Cleaning user data will remove all activity logs as well as all input data entered by this user. This cannot be undone.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Clean User Data",
                    "url" => $this->model->get_link_url("userUpdate",
                        array("uid" => $this->selected_user['id'],
                            "mode" => "clean")),
                    "type" => "danger",
                )),
            )
        ));
        $card->output_content();
    }

    /**
     * Render the card to send activation email to the user.
     */
    private function output_user_send_activation_email()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Email Activation",
            "type" => "info",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "This will send an activation email to the user",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Send Activation Email",
                    "url" => $this->model->get_link_url("userUpdate",
                        array("uid" => $this->selected_user['id'],
                            "mode" => "activation_email")),
                    "type" => "info",
                )),
            )
        ));
        $card->output_content();
    }

    /**
     * Render the card to delete a button.
     */
    private function output_user_delete()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Delete User",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "Deleting a user will remove all data associated to this user. This cannot be undone.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("button", array(
                    "label" => "Delete User",
                    "url" => $this->model->get_link_url("userDelete",
                        array("uid" => $this->selected_user['id'])),
                    "type" => "danger",
                )),
            )
        ));
        $card->output_content();
    }

    /**
     * Render the card to manipulate user groups.
     */
    private function output_user_groups()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "User Groups",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "When assigned to a user, groups provide the user with a predefined set of access rights.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("sortableList", array(
                    "is_editable" => $this->model->can_modify_user(),
                    "items" => $this->model->get_selected_user_groups(),
                    "url_add" => $this->model->get_link_url(
                        "userUpdate",
                        array(
                            "uid" => $this->selected_user['id'],
                            "mode" => "add_group",
                        )
                    ),
                    "url_delete" => $this->model->get_link_url(
                        "userUpdate",
                        array(
                            "uid" => $this->selected_user['id'],
                            "mode" => "rm_group",
                            "did" => ":did",
                        )
                    ),
                    "label_add" => "Add Group",
            )))
        ));
        $card->output_content();
    }


    /**
     * Render the cards to manipulate a user, i.e. block, unblock. add/remove
     * groups, or delete a user.
     */
    private function output_user_manipulation()
    {
        $this->output_user_groups();
        if($this->model->can_modify_user())
        {
            $this->output_user_send_activation_email();
            if(!$this->selected_user["blocked"])
                $this->output_user_blocking();
            else
                $this->output_user_unblocking();
            $this->output_user_clean();
        }
        if($this->model->can_delete_user())
            $this->output_user_delete();
    }

    /**
     * Render the card to unblock the user.
     */
    private function output_user_unblocking()
    {
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Unblock User",
            "type" => "warning",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "Unblocking a user will revert the state of the user to what it was befor the blocking took place.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("button", array(
                    "label" => "Unblock User",
                    "url" => $this->model->get_link_url("userUpdate",
                        array("uid" => $this->selected_user['id'],
                            "mode" => "unblock")),
                    "type" => "warning",
                )),
            )
        ));
        $card->output_content();
    }

    /**
     * Render the list of users and their activity.
     */
    protected function output_user_activity()
    {
        require __DIR__ . "/tpl_user_activity.php";
    }

    /**
     * Render the activity table content.
     */
    private function output_user_activity_rows()
    {   
        $pc = $this->model->calc_pages_for_progress();
        foreach($this->model->fetch_users() as $user)
        {
            $id = $user['id'];
            $url = $this->model->get_link_url("userSelect", array("uid" => $id));
            $email = $user['email'];
            $user_name = $user['name'];
            if ($user['blocked'] == 1) {
                $state = 'blocked';
            } else {
                $state = $user['status'];
            }
            $desc = $user['description'];
            $groups = $user['groups'];
            $row_state = "";
            if(strpos($state, "blocked") !== false)
                $row_state = "table-warning";
            $code = $user['code'];
            $last_login = $user['last_login'];
            $activity = $user['user_activity'];
            $ac = $user['ac'];
            if($pc === 0 || $ac > $pc){
                $progress = 1;
            } else {
                $progress = $ac/$pc;
            }
            require __DIR__ . "/tpl_user_activity_row.php";
        }
    }

    /**
     * Render the user progress bar.
     *
     * @param float $progress
     *  The progress of the user (a value between 0 and 1).
     */
    private function output_user_progress_bar($progress)
    {
        $bar = new BaseStyleComponent('progressBar', array(
            'count' => round($progress * 100),
            'count_max' => 100,
            'is_striped' => false,
        ));
        $bar->output_content();
    }

    /**
     * Render the user description or the intro text.
     */
    private function output_main_content()
    {
        if($this->selected_user != null)
        {
            $state = $this->selected_user['status'];
            $code = $this->selected_user['code'] ?? "-";
            $user_name = $this->selected_user['name'];
            $desc = $this->selected_user['description'];
            $groups = $this->selected_user['groups'];
            $last_login = $this->selected_user['last_login'];
            $activity = $this->selected_user['user_activity'];
            $progress = $this->model->get_user_progress($this->selected_user['id'], $this->model->calc_pages_for_progress());
            require __DIR__ . "/tpl_user.php";
        }
        else
        {
            require __DIR__ . "/tpl_users.php";
        }
    }

    /**
     * Render the ACL list.
     */
    private function output_user_acl()
    {
        $acl = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => false,
            "is_collapsible" => true,
            "title" => "ACL",
            "children" => array(new BaseStyleComponent("acl", array(
                "title" => "Page",                
                "items" => $this->model->get_acl_selected_user()
            )))
        ));
        $acl->output_content();
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
        $local = array(__DIR__ . "/css/users.css");
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
        if(empty($local)){
            $local = array(__DIR__ . "/js/users.js");
        }
        return parent::get_js_includes($local);
    }

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
