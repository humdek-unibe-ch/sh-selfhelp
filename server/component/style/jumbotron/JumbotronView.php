<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the jumbotron style component.
 * This is a visual container with large padding spaces.
 */
class JumbotronView extends StyleView
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
