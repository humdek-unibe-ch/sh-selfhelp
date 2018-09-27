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
     * DB field 'caption' (empty string).
     * The question that can be answered with right or wrong.
     */
    private $title;

    /**
     * DB field 'label_right' (empty string).
     * The label of the right button.
     */
    private $right_label;

    /**
     * DB field 'label_wrong' (empty string).
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

    /**
     * DB field 'type' ('light').
     * The style of the card. E.g. 'warning', 'danger', etc.
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
        $this->title = $this->model->get_db_field("caption");
        $this->right_label = $this->model->get_db_field("label_right");
        $this->wrong_label = $this->model->get_db_field("label_wrong");
        $this->right_content = $this->model->get_db_field("right_content");
        $this->wrong_content = $this->model->get_db_field("wrong_content");
        $this->type = $this->model->get_db_field("type", "info");
        $this->add_local_component("quiz-container",
            new BaseStyleComponent("tabs", array("children" => array(
                new BaseStyleComponent("tab", array(
                    "label" => $this->right_label,
                    "children" => array(
                        new BaseStyleComponent("markdown", array(
                            "text_md" => $this->right_content,
                        )),
                    ),
                    "type" => $this->type,
                )),
                new BaseStyleComponent("tab", array(
                    "label" => $this->wrong_label,
                    "children" => array(
                        new BaseStyleComponent("markdown", array(
                            "text_md" => $this->wrong_content,
                        )),
                    ),
                    "type" => $this->type,
                )),
            )))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the tabs.
     */
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
