<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the style component. Each style is wrapped in a div
 * container which serves to identify styles by id. This feature is used in the
 * CMS to highlight the selected style.
 */
class StyleWrapperView extends BaseView
{
    private $style;
    private $id;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     * @param object $style
     *  The style component to be rendered.
     */
    public function __construct($style, $id)
    {
        parent::__construct();
        $this->style = $style;
        $this->id = $id;
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $highlight = "";
        if($this->id === $_SESSION['active_section_id'])
           $highlight = "highlight";
        require __DIR__ . "/tpl_style.php";
    }
}
?>
