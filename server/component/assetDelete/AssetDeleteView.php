<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset delete component.
 */
class AssetDeleteView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * Specifies the insert mode (either 'css' or 'asset').
     */
    private $mode;

    /**
     * The file name of the file to be deleted.
     */
    private $file_name;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance.
     * @param object $controller
     *  The controller instance.
     * @param string $mode
     *  Specifies the insert mode (either 'css' or 'asset').
     * @param string $name
     *  The file name of the file to be deleted.
     */
    public function __construct($model, $controller, $mode, $name)
    {
        parent::__construct($model, $controller);
        $this->mode = $mode;
        $this->file_name = $name;
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
     * Render the from to remove a file from the server.
     */
    private function output_form_rm_file()
    {
        $form = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "is_expanded" => true,
            "is_collapsible" => false,
            "title" => "Delete File",
            "children" => array(
                new BaseStyleComponent("form", array(
                    "label" => "Delete File",
                    "url" => $this->model->get_link_url("assetDelete",
                        array(
                            "mode" => $this->mode,
                            "did" => $this->file_name,
                        )
                    ),
                    "url_cancel" => $this->model->get_link_url("assetSelect"),
                    "children" => array(
                        new BaseStyleComponent("input", array(
                            "type_input" => "hidden",
                            "name" => "rm_file",
                            "value" => $this->file_name,
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
        $url = $this->model->get_link_url("assetSelect");
        if($this->controller->has_succeeded())
            require __DIR__ . "/tpl_success_rm_file.php";
        else
            require __DIR__ . "/tpl_rm_file.php";
    }
}
?>
