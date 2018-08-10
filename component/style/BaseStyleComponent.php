<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/BaseStyleModel.php";
require_once __DIR__ . "/button/ButtonView.php";
require_once __DIR__ . "/card/CardView.php";
require_once __DIR__ . "/link/LinkView.php";
require_once __DIR__ . "/jumbotron/JumbotronView.php";
require_once __DIR__ . "/title/TitleView.php";
require_once __DIR__ . "/plaintext/PlaintextView.php";
require_once __DIR__ . "/alert/AlertView.php";
require_once __DIR__ . "/figure/FigureView.php";
require_once __DIR__ . "/video/VideoView.php";
require_once __DIR__ . "/quiz/QuizView.php";
require_once __DIR__ . "/nestedList/NestedListView.php";
require_once __DIR__ . "/accordionList/AccordionListView.php";
require_once __DIR__ . "/descriptionList/DescriptionListView.php";
require_once __DIR__ . "/progressBar/ProgressBarView.php";

/**
 * The class to define the base style component. A base style component serves
 * to render content in different views. The views are specified by the style.
 */
class BaseStyleComponent extends BaseComponent
{
    private $model;
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the BaseStyleModel class and the
     * a StyleView class according to the style parameter. It passes the view
     * instance to the constructor of the parent class.
     *
     * @param string $style
     *  The style of the component.
     * @param bool $fluid
     *  If set to true the jumbotron gets the bootstrap class "container-fluid",
     *  othetwise the class "container" is used. The default is false.
     * @param object $nav
     *  The section navigation component.
     */
    public function __construct($style, $fluid=false, $nav=null)
    {
        $this->model = new BaseStyleModel();
        if($style == "button")
            $view = new ButtonView($this->model);
        else if($style == "card")
            $view = new CardView($this->model, $fluid);
        else if($style == "link")
            $view = new LinkView($this->model);
        else if($style == "jumbotron")
            $view = new JumbotronView($this->model, $fluid);
        else if($style == "plaintext")
            $view = new PlaintextView($this->model);
        else if($style == "title")
            $view = new TitleView($this->model);
        else if($style == "alert")
            $view = new AlertView($this->model, $fluid);
        else if($style == "figure")
            $view = new FigureView($this->model);
        else if($style == "video")
            $view = new VideoView($this->model);
        else if($style == "quiz")
            $view = new QuizView($this->model);
        else if($style == "nested_list")
            $view = new NestedListView($this->model);
        else if($style == "accordion_list")
            $view = new AccordionListView($this->model);
        else if($style == "description_list")
            $view = new DescriptionListView($this->model);
        else if($style == "progress")
            $view = new ProgressBarView($this->model);
        else
            throw new Exception("unknown style '$style'");
        parent::__construct($view);
    }

    /**
     * Set the fields that are required by the component model.
     *
     * @param array $fields
     *  An array containing fields for the view to render to content. The
     *  required are dependent of the style. The array must contain key, value
     *  pairs where the key is the name of the field and the value the content
     *  of the field.
     */
    public function set_fields($fields)
    {
        $this->model->set_fields($fields);
    }

    /**
     * Set the fields that are required by the component model.
     *
     * @param array $fields
     *  An array containing fields for the view to render to content. The
     *  required are dependent of the style. The array must contain key, value
     *  pairs where the key is the name of the field and the value the content
     *  of the field.
     */
    public function set_fields_full($fields)
    {
        $this->model->set_fields_full($fields);
    }
}
?>
