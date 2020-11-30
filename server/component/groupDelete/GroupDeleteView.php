<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the group delete component.
 */
class GroupDeleteView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * An array of group properties (see UserModel::fetch_group).
     */
    private $selected_group;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user delete component.
     * @param object $controller
     *  The controller instance of the user delete component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->selected_group = $this->model->get_selected_group();
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
     * Rednet the delete form.
     */
    private function output_form()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Delete Group",
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the name of the group.",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Delete Group",
                    "url" => $this->model->get_link_url("groupDelete",
                        array("gid" => $this->selected_group['id'])),
                    "type" => "danger",
                    "url_cancel" => $this->model->get_link_url("groupSelect",
                        array("gid" => $this->selected_group['id'])),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "name",
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter Group Name",
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $url = $this->model->get_link_url("groupSelect");
            require __DIR__ . "/tpl_success.php";
        }
        else
            require __DIR__ . "/tpl_group_delete.php";
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
