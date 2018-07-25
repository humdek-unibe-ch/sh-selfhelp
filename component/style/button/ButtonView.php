<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the button style component.
 */
class ButtonView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $label = $this->model->get_db_field("label");
        $url = $this->model->get_db_field("url");
        if($url == "") return;
        require __DIR__ . "/tpl_button.php";
    }
}
?>
