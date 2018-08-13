<?php
require_once __DIR__ . "/../../BaseView.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/service/Parsedown.php";

/**
 * The view class of the plaintext style component.
 * A plaintext style supports the following fields:
 *  'text': The text to be rendered.
 */
class PlaintextView extends BaseView
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
        echo htmlspecialchars($this->model->get_db_field('text'));
    }
}
?>
