<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the markdown component.
 */
class MarkdownView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'text_markdown' (empty string).
     * The text to be rendered as markdown content.
     */
    private $text_markdown;

    /**
     * DB field 'is_paragraph' (false).
     * If set to true the text is wrapped in paragraph tags. If set to false the
     * text is rendered as is.
     */
    private $is_paragraph;

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
        $this->text_markdown = $this->model->get_db_field('text_markdown');
        $this->is_paragraph = $this->model->get_db_field('is_paragraph', false);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        if($this->is_paragraph)
            echo "<p>" . $this->text_markdown . "</p>";
        else
            echo $this->text_markdown;
    }
}
?>
