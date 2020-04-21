<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";
require_once __DIR__ . "/../style/StyleComponent.php";

/**
 * The delete view class of the cms component.
 */
class CmsDeleteView extends BaseView
{
    /**
     * A string describing the target: either a section or a page.
     */
    private $target;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the cms component.
     * @param object $controller
     *  The controller instance of the cms delete component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        if($this->model->get_active_section_id() == null)
            $this->target = "page";
        else
            $this->target = "section";
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
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Delete " . ucfirst($this->target),
            "type" => "danger",
            "css" => "mb-3",
            "children" => array(
                new BaseStyleComponent("plaintext", array(
                    "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the name of the " . $this->target . ".",
                    "is_paragraph" => true,
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Delete " . ucfirst($this->target),
                    "url" => $this->model->get_link_url("cmsDelete",
                        array(
                            "pid" => $this->model->get_active_page_id(),
                            "sid" => $this->model->get_active_section_id(),
                        )
                    ),
                    "type" => "danger",
                    "url_cancel" => $this->model->get_link_url("cmsSelect",
                        $this->model->get_current_url_params()),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "name",
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter the Name",
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms delete view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $id = null;
            if($this->model->get_active_section_id() != null)
                $id = $this->model->get_active_page_id();
            $url = $this->model->get_link_url("cmsSelect", array("pid" => $id));
            $name = $this->controller->get_deleted_name();
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            if($this->model->get_active_section_id() == null)
            {
                $info = $this->model->get_page_info();
                $name = $info["keyword"];
            }
            else
            {
                $info = $this->model->get_section_info();
                $name = $info["name"];
            }
            require __DIR__ . "/tpl_cms_delete.php";
        }
    }
}
?>
