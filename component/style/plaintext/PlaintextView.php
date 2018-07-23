<?php
require_once __DIR__ . "/../../BaseView.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/service/Parsedown.php";

/**
 * The view class of the plaintext style component.
 */
class PLaintextView extends BaseView
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
        $parsedown = new Parsedown();
        echo $parsedown->text($this->model->get_db_field('text'));
    }
}
?>
