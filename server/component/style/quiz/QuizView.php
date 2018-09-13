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
        $this->right_label = $this->model->get_db_field("right_label");
        $this->wrong_label = $this->model->get_db_field("wrong_label");
        $this->right_content = $this->model->get_db_field("right_content");
        $this->wrong_content = $this->model->get_db_field("wrong_content");
        $this->add_local_component("quiz-container",
            new BaseStyleComponent("tabs", array("children" => array(
                new BaseStyleComponent("tab", array(
                    "label" => $this->right_label,
                    "children" => array(
                        new BaseStyleComponent("markdown", array(
                            "text_markdown" => $this->right_content,
                        )),
                    ),
                    "type" => "info",
                )),
                new BaseStyleComponent("tab", array(
                    "label" => $this->wrong_label,
                    "children" => array(
                        new BaseStyleComponent("markdown", array(
                            "text_markdown" => $this->wrong_content,
                        )),
                    ),
                    "type" => "info",
                )),
            )))
        );
    }

    /* Private Methods ********************************************************/

    private function output_tabs()
    {
        $this->output_local_component("quiz-container");
    }

    /* Public Methods *********************************************************/

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
