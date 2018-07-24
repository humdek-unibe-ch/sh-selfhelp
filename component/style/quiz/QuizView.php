<?php
require_once __DIR__ . "/../../BaseView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/service/Parsedown.php";

/**
 * The view class of the quiz style component.
 */
class QuizView extends BaseView
{
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
    public function get_css_includes()
    {
        return parent::get_css_includes() + array(
            __DIR__ . "/quiz.css"
        );
    }

    /**
     * Get js include files required for this component. This overrides the
     * parent implementation.
     *
     * @retval array
     *  An array of js include files the component requires.
     */
    public function get_js_includes()
    {
        return parent::get_js_includes() + array(
            __DIR__ . "/quiz.js"
        );
    }

    /**
     * Render the style view.
     */
    public function output_content()
    {
        $parsedown = new Parsedown();
        $title = $parsedown->text($this->model->get_db_field("title"));
        $id = $this->model->get_db_field("id");
        $right_label = $this->model->get_db_field("right_label");
        $wrong_label = $this->model->get_db_field("wrong_label");
        $right_content = $parsedown->text(
            $this->model->get_db_field("right_content"));
        $wrong_content = $parsedown->text(
            $this->model->get_db_field("wrong_content"));
        require __DIR__ . "/tpl_quiz.php";
    }
}
?>
