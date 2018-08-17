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
    }

    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        echo $this->text_markdown;
    }
}
?>
