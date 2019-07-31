<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the exportDelete component.
 */
class ExportDeleteView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
    }

    /* Private Methods ********************************************************/

    /**
     * Render the description text.
     */
    private function output_text()
    {
        $md = new BaseStyleComponent('markdown', array(
            "text_md" => $this->model->get_text(),
        ));
        $md->output_content();
    }

    /**
     * Render the delete form.
     */
    private function output_form()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Remove All User " . $this->model->get_title(),
            "type" => "danger",
            "children" => array(
                new BaseStyleComponent("markdown", array(
                    "text_md" => "You must be absolutely certain that this is what you want. This operation cannot be undone! To verify, type `" . $this->model->get_veryfication_str() . "` in the input field below.",
                )),
                new BaseStyleComponent("form", array(
                    "label" => "Remove All User " . $this->model->get_title(),
                    "url" => $this->model->get_link_url("exportDelete",
                        array("selector" => $this->model->get_selector())),
                    "type" => "danger",
                    "url_cancel" => $this->model->get_link_url("export"),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "text",
                            "name" => "veryfication",
                            "is_required" => true,
                            "css" => "mb-3",
                            "placeholder" => "Enter " . $this->model->get_veryfication_str() . " to verify",
                        )),
                    )
                )),
            )
        ));
        $form->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the component view.
     */
    public function output_content()
    {
        $title = $this->model->get_title();
        if($this->controller->has_succeeded())
        {
            $url = $this->model->get_link_url("export");
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            require __DIR__ . "/tpl_export_delete.php";
        }
    }
}
?>
