<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the link style component.
 */
class LinkView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'url' (empty string).
     * The target of the link. If the url is not set, the link will not be
     * rendered.
     */
    private $url;

    /**
     * DB field 'label' (empty string).
     * The name that will be displayed. If the name is not set, the url is used
     * as name.
     */
    private $label;

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
        $this->url = $this->model->get_db_field("url");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->url == "") return;
        if($this->label == "") $this->label = htmlspecialchars($this->url);
        require __DIR__ . "/tpl_link.php";
    }
}
?>
