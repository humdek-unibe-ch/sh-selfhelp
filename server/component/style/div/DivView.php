<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the div style component.
 * A div style is a container that allows to wrap content into a div tag.
 */
class DivView extends StyleView
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
        require __DIR__ . "/tpl_div.php";
    }
}
?>
