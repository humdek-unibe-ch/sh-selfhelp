<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the user insert component.
 */
class UserInsertView extends BaseView
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
     *  The model instance of the user insert component.
     * @param object $controller
     *  The controller instance of the user insert component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
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
     * Render the group selection from.
     */
    private function output_group_selection()
    {
        $select = new BaseStyleComponent("select", array(
            "name" => "user_groups[]",
            "is_multiple" => true,
            "live_search" => true,
            "items" => $this->model->get_group_options(),
        ));
        $select->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $user = $this->controller->get_new_email();
            $url_user = $this->model->get_link_url("userSelect",
                array("uid" => $this->controller->get_new_uid()));
            $url_users = $this->model->get_link_url("userSelect");
            $url_new = $this->model->get_link_url("userInsert");
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $action_url = $this->model->get_link_url("userInsert");
            $cancel_url = $this->model->get_link_url("userSelect");
            require __DIR__ . "/tpl_insert_user.php";
        }
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
