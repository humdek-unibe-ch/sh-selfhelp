<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the conditional container style component.
 * A conditional containers wraps its content into a div tag but only displays
 * the content if a given condition is true.
 */
class ConditionalContainerView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'condition' (empty string).
     * A condition string that needs to be parsed and evaluated.
     */
    private $condition;

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
        $this->condition = $this->model->get_db_field('condition');
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $res = $this->model->compute_condition($this->condition);
        if($this->model->is_cms_page() || $res)
            require __DIR__ . "/tpl_container.php";
    }
}
?>
