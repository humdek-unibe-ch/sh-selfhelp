<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the quiz style component.
 */
class QuizView extends BaseView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'quiz_title' (empty string).
     * The question that can be answered with right or wrong.
     */
    private $title;

    /**
     * Internal field 'id' (the section id).
     * This is a field that is set internally and does not come from the DB.
     */
    private $id;

    /**
     * DB style field 'right_label'.
     * The label of the right button.
     */
    private $right_label;

    /**
     * DB style field 'wrong_label'.
     * The label of the wrong button.
     */
    private $wrong_label;

    /**
     * DB field 'right_content' (empty string).
     * The text that is dispayed when clicking on the button 'right'.
     */
    private $right_content;

    /**
     * DB field 'wrong_contnet' (empty string).
     * The text that is dispayed when clicking on the button 'wrong'.
     */
    private $wrong_content;

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
        $this->title = $this->model->get_db_field("quiz_title");
        $this->id = $this->model->get_db_field("id");
        $this->right_label = $this->model->get_db_field("right_label");
        $this->wrong_label = $this->model->get_db_field("wrong_label");
        $this->right_content = $this->model->get_db_field("right_content");
        $this->wrong_content = $this->model->get_db_field("wrong_content");
    }

    /* Private Methods ********************************************************/

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
        $local = array(__DIR__ . "/quiz.css");
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
        $local = array(__DIR__ . "/quiz.js");
        return parent::get_js_includes($local);
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        if($this->title == "") return;
        require __DIR__ . "/tpl_quiz.php";
    }
}
?>
