<?php
require_once __DIR__ . "/../../BaseView.php";

/**
 * The view class of the plaintext style component.
 * A plaintext style supports the following fields:
 *  'text': The text to be rendered.
 */
class PlaintextView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'text' (empty string).
     * The text to be rendered.
     */
    private $text;

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
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->text = $this->model->get_db_field('text');
        $this->is_paragraph = $this->model->get_db_field('is_paragraph', false);
    }

    /* Public Methods *********************************************************/

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->is_paragraph)
            echo "<p>" . htmlspecialchars($this->text) . "</p>";
        else
            echo htmlspecialchars($this->text);
    }
}
?>
