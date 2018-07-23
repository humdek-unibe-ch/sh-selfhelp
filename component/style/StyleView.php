<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the style component.
 */
class StyleView extends BaseView
{
    /* Private Properties *****************************************************/

    private $fluid;
    private $children;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the footer component.
     * @param bool $fluid
     *  If set to true the content will be rendered in a container-fluid
     *  bootstrap element, if set to false in a container.
     */
    public function __construct($model, $children, $fluid)
    {
        parent::__construct($model);
        $this->children = $children;
        $this->fluid = $fluid;
    }

    /* Private Methods ********************************************************/

    /**
     * A simple wrapper for the same method of the model instance. See
     * StyleModel::get_db_field($key).
     *
     * @param string $key
     *  The field name.
     * @retval string
     *  The content of the filed specified by the key.
     */
    private function get_db_field($key)
    {
        return $this->model->get_db_field($key);
    }

    /**
     * Render the content of the style view. The content is composed of child
     * sections.
     */
    private function output_section_content()
    {
        foreach($this->children as $child)
            $child->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $fluid = ($this->fluid) ? "-fluid" : "";
        $tpl_name = $this->model->get_tpl_name();
        require_once __DIR__ . "/tpl_" . $tpl_name . ".php";
    }
}
?>
