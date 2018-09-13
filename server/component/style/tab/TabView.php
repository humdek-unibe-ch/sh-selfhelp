<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the tab style component.
 */
class TabView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'label' (empty string).
     * The label of the tab button. If this field id not set, the tab is not
     * rendered.
     */
    private $label;

    /**
     * DB field 'type' ('info').
     * The style of the button.
     */
    private $type;

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
        $this->label = $this->model->get_db_field("label");
        $this->type = $this->model->get_db_field("type", "info");
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->label == "") return;
        require __DIR__ . "/tpl_tab.php";
    }
}
?>
