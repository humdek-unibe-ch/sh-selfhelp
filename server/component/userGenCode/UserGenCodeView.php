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
    }

    /**
     * Render the number of existing validation codes in the database.
     */
    private function output_codes()
    {
        $count = $this->model->get_code_count();
        if($count === 0)
            return;
        $count_consumed = $this->model->get_code_count_consumed();
        $count_open = $count - $count_consumed;
        require __DIR__ . "/tpl_code_counts.php";
    }

    /**
     * Render the validation code export buttons.
     */
    private function output_export_buttons()
    {
        $fields = $this->model->get_export_button_fields();
        foreach($fields['options'] as $option)
        {
            $button = new BaseStyleComponent('button', array(
                'url' => $option['url'],
                'label' => $option['label'],
                'type' => $option['type'],
            ));
            $button->output_content();
            echo "\n";
        }
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
            $url = $this->model->get_link_url('userGenCode');
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
