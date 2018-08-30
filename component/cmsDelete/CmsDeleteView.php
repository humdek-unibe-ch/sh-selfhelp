<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";
require_once __DIR__ . "/../style/StyleComponent.php";

/**
 * The insert view class of the cms component.
 */
class CmsDeleteView extends BaseView
{
    private $target;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the cms component.
     * @param object $controller
     *  The controller instance of the cms component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        if($this->model->get_active_section_id() == null)
            $target = "page";
        else
            $target = "section";
        $this->target = $target;
        $this->add_local_component("alert-fail",
            new BaseStyleComponent("alert", array(
                "type" => "danger",
                "children" => array(new BaseStyleComponent("plaintext", array(
                    "text" => "Failed to delete the " . $target . ".",
                )))
            ))
        );
        $this->add_local_component("form",
            new BaseStyleComponent("card", array(
                "is_expanded" => true,
                "is_collapsible" => false,
                "title" => "Delete " . ucfirst($target),
                "type" => "danger",
                "children" => array(
                    new BaseStyleComponent("plaintext", array(
                        "text" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, enter the name of the " . $target . ".",
                        "is_paragraph" => true,
                    )),
                    new BaseStyleComponent("form", array(
                        "label" => "Delete " . ucfirst($target),
                        "url" => $this->model->get_link_url("cmsDelete",
                            array(
                                "pid" => $this->model->get_active_page_id(),
                                "sid" => $this->model->get_active_section_id(),
                            )
                        ),
                        "type" => "danger",
                        "cancel" => true,
                        "cancel_url" => $this->model->get_link_url("cmsSelect",
                            $this->model->get_current_url_params()),
                        "children" => array(
                            new BaseStyleComponent("input", array(
                                "type" => "name",
                                "name" => "name",
                                "is_required" => true,
                                "css" => "mb-3",
                                "placeholder" => "Enter the Name",
                            )),
                        )
                    )),
                )
            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the fail alerts.
     */
    private function output_alert()
    {
        if($this->controller->has_failed())
            $this->output_local_component("alert-fail");
    }

    /**
     * Rednet the delete form.
     */
    private function output_form()
    {
        $this->output_local_component("form");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
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
