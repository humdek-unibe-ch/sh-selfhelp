<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the jumbotron style component.
 */
class JumbotronView extends BaseView
{
    private $fluid;
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     * @param bool $fluid
     *  If set to true the jumbotron gets the bootstrap class "container-fluid",
     *  othetwise the class "container" is used.
     */
    public function __construct($model, $fluid)
    {
        $this->fluid = $fluid;
        parent::__construct($model);
    }

    /**
     * Render the content of the style view. The content is composed of child
     * sections.
     */
    private function output_section_content()
    {
        $children = $this->model->get_db_field("children");
        foreach($children as $child)
            $child->output_content();
    }


    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fluid = ($this->fluid) ? "-fluid" : "";
        require __DIR__ . "/tpl_jumbotron.php";
    }
}
?>
