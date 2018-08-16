<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the raw text style component. This component renders text
 * into a pre and code tag.
 * The following keys are required:
 * 'text': The contnet of to be rendered.
 */
class RawTextView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of a base style component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Get css include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of css include files the component requires.
     */
    public function get_css_includes($local = array())
    {
        $local = array(__DIR__ . "/rawText.css");
        return parent::get_css_includes($local);
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $content = $this->model->get_db_field("text");
        require __DIR__ . "/tpl_raw_text.php";
    }
}
?>
