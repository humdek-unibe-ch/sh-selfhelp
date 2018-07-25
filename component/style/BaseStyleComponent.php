<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/BaseStyleModel.php";
require_once __DIR__ . "/button/ButtonView.php";
require_once __DIR__ . "/link/LinkView.php";
require_once __DIR__ . "/jumbotron/JumbotronView.php";
require_once __DIR__ . "/title/TitleView.php";
require_once __DIR__ . "/plaintext/PlaintextView.php";
require_once __DIR__ . "/alert/AlertView.php";
require_once __DIR__ . "/figure/FigureView.php";
require_once __DIR__ . "/video/VideoView.php";
require_once __DIR__ . "/video/VideoSourceView.php";
require_once __DIR__ . "/quiz/QuizView.php";

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
     */
    public function __construct($style, $fluid=false)
    {
        $styles = explode('-', $style);
        $this->model = new BaseStyleModel();
        if($styles[0] == "button")
            $view = new ButtonView($this->model);
        else if($styles[0] == "link")
            $view = new LinkView($this->model);
        else if($styles[0] == "jumbotron")
            $view = new JumbotronView($this->model, $fluid);
        else if($styles[0] == "plaintext")
            $view = new PlaintextView($this->model);
        else if($styles[0] == "title")
        {
            $level = intval($styles[1]);
            $view = new TitleView($this->model, $level);
        }
        else if($styles[0] == "alert")
        {
            $type = $styles[1];
            $view = new AlertView($this->model, $type, $fluid);
        }
        else if($styles[0] == "figure")
            $view = new FigureView($this->model);
        else if($styles[0] == "video")
            $view = new VideoView($this->model);
        else if($styles[0] == "video_source")
            $view = new VideoSourceView($this->model);
        else if($styles[0] == "quiz")
            $view = new QuizView($this->model);
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
}
?>
