<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the validation code generation component.
 */
class UserGenCodeView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
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
     * Render the fail alerts.
     */
    private function output_alert()
    {
        $this->output_controller_alerts_fail();
        $this->output_warning();
    }

    /**
     * Render the fail alerts.
     */
    private function output_warning()
    {
        $count = $this->model->get_code_count();
        if($count === 0) return;
        $alert = new BaseStyleComponent("alert", array(
            "type" => "warning",
            "is_dismissable" => true,
            "children" => array(new BaseStyleComponent("markdownInline", array(
                "text_md_inline" => "The database already holds `" . $count . "` validation codes. If further codes are added, the uniquness of each code **can no longer be guaranteed**.",
            )))
        ));
        $alert->output_content();
    }

    /**
     * Render the number of collision codes.
     */
    private function output_collision()
    {
        $final_count = $this->controller->get_final_count();
        $count = $this->controller->get_requested_count();
        if($final_count == $count) return;
        $count_collision = $count - $final_count;
        require __DIR__ . "/tpl_collision.php";
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        if($this->controller->has_succeeded())
        {
            $count = $this->controller->get_final_count();
            $url = $this->model->get_link_url('exportData',
                array('selector' => 'validation_codes'));
            require __DIR__ . "/tpl_success.php";
        }
        else
        {
            $action_url = $this->model->get_link_url("userGenCode");
            $cancel_url = $this->model->get_link_url("userSelect");
            require __DIR__ . "/tpl_generate_codes.php";
        }
    }
}
?>
