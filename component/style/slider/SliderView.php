<?php
require_once __DIR__ . "/../formField/FormFieldView.php";

/**
 * The view class of the slider form style component.
 */
class SliderView extends FormFieldView
{
    /* Private Properties******************************************************/

    /**
     * DB field 'min' (0).
     * The minimal value of the slider.
     */
    private $min;

    /**
     * DB field 'max' (10).
     * The maximal value of the slider.
     */
    private $max;

    /**
     * DB field 'count' (5).
     * The default value of the slider.
     */
    private $count;

    /**
     * DB field 'labels' (empty array).
     * The legend rendered below the slider.
     */
    private $labels;

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
        $this->min = $this->model->get_db_field("min", 0);
        $this->max = $this->model->get_db_field("max", 10);
        $this->count = $this->model->get_db_field("count", 5);
        $this->labels = $this->model->get_db_field("labels", array());
    }

    /* Private Methods ********************************************************/

    /**
     * Render the slider legend.
     */
    private function output_legend()
    {
        if(!is_array($this->labels)) return;
        for($idx = 0; $idx <= ($this->max - $this->min); $idx++)
        {
            $text = "unknown";
            if(array_key_exists($idx, $this->labels))
                $text = htmlspecialchars($this->labels[$idx]);
            require __DIR__ . "/tpl_legend_item.php";
        }
    }

    /**
     * Rendere the slider.
     */
    protected function output_form_field()
    {
        $css = ($this->label == "") ? $this->css : "";
        require __DIR__ . "/tpl_slider.php";
    }

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
        $local = array(
            __DIR__ . "/slider.css",
        );
        return parent::get_css_includes($local);
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes($local = array())
    {
        $local = array(
            __DIR__ . "/slider.js",
        );
        return parent::get_js_includes($local);
    }
}
?>
