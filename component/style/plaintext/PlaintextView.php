<?php
require_once __DIR__ . "/../../BaseView.php";

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
        $paragraphs = preg_split("/\R\R+/", $this->model->get_db_field('text'));
        foreach($paragraphs as $text)
            require __DIR__ . "/tpl_plaintext.php";
    }
}
?>
