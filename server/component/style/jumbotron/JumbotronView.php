<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the jumbotron style component.
 */
class JumbotronView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
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
        require __DIR__ . "/tpl_jumbotron.php";
    }
}
?>
