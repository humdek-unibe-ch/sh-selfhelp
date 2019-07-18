<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the mermaid inline component.
 */
class MermaidView extends StyleView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'code' (empty string).
     * The text to be rendered as markdown content.
     */
    private $code_text;

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->code_text = $this->model->get_db_field('code');
    }

    /* Public Methods *********************************************************/

    /**
     * Render the mermaid view.
     */
    public function output_content()
    {
        $code = $this->code_text;          
        require __DIR__ . "/tpl_mermaid.php";
    }
}
?>
