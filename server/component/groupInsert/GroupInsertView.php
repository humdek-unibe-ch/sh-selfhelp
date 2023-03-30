<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the group insert component.
 */
class GroupInsertView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * What group type we create
     */
    private $group_type;

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user insert component.
     * @param object $controller 
     *  The controller instance of the user insert component.
     * @param string $group_type
     * The group type that we want to create: group or db_role
     */
    public function __construct($model, $controller, $group_type)
    {
        parent::__construct($model, $controller);
        $this->group_type = $group_type;
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
    }

    /**
     * Render the ACL form.
     */
    private function output_group_acl()
    {
        $acl = new BaseStyleComponent("acl", array(
            "title" => "Function",
            "is_editable" => true,
            "items" => $this->model->get_simple_acl_selected_group(),
            "items_granted" => $this->model->get_simple_acl_current_user(),
        ));
        $acl->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $group = $this->controller->get_new_name();
            $url = $this->model->get_link_url("groupSelect",
                array("gid" => $this->controller->get_new_gid()));
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $action_url = $this->model->get_link_url("groupInsert", array("type"=>$this->group_type));
            $cancel_url = $this->model->get_link_url("groupSelect");
            require __DIR__ . "/tpl_insert_group.php";
        }
    }

    /**
     * Output DB role Jumbotron
     */
    public function output_jumbotron()
    {
        if ($this->group_type == groupTypes_db_role) {
            require __DIR__ . "/tpl_db_role_jumbotron.php";
        }else if ($this->group_type == groupTypes_group) {
            require __DIR__ . "/tpl_group_jumbotron.php";
        }
    }

    /**
     * Output acl for db role
     */
    public function output_acl_tmpl()
    {
        if ($this->group_type == groupTypes_db_role) {
            require __DIR__ . "/tpl_group_acl.php";
        }
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
