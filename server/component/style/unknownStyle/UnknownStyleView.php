<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the unknown style component.
 */
class UnknownStyleView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'style_name' (empty string).
     * The name of the style that cannot be found.
     */
    private $style_name;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     */
    public function __construct($model)
    {
        $this->style_name = $model->get_db_field("style_name");
        parent::__construct($model);
    }


    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_unknown.php";
    }
}
?>
