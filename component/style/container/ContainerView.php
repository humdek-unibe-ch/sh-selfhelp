<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the container style component.
 */
class ContainerView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'is_fluid' (true).
     * If set to true the container spand to whole page. If set to false the
     * container only uses a part of the page.
     */
    private $is_fluid;

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
        $this->is_fluid = $this->model->get_db_field('is_fluid', true);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fluid = ($this->is_fluid) ? "-fluid" : "";
        require __DIR__ . "/tpl_container.php";
    }
}
?>
