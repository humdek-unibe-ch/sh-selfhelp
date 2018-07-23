<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/BaseStyleModel.php";
require_once __DIR__ . "/button/ButtonView.php";
require_once __DIR__ . "/link/LinkView.php";
require_once __DIR__ . "/jumbotron/JumbotronView.php";
require_once __DIR__ . "/title/TitleView.php";
require_once __DIR__ . "/plaintext/PlaintextView.php";

/**
 * The class to define the base style component. A base style component serves
 * to render content in different views. The views are specified by the style.
 */
class BaseStyleComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the BaseStyleModel class and the
     * a StyleView class according to the style parameter. It passes the view
     * instance to the constructor of the parent class.
     *
     * @param string $style
     *  The style of the component.
     * @param array $fields
     *  An array containing fields for the view to render to content. The
     *  required are dependent of the style. The array must contain key, value
     *  pairs where the key is the name of the field and the value the content
     *  of the field.
     */
    public function __construct($style, $fields, $fluid=false)
    {
        $model = new BaseStyleModel($fields);
        if($style == "button")
            $view = new ButtonView($model);
        else if($style == "link")
            $view = new LinkView($model);
        else if($style == "jumbotron")
            $view = new JumbotronView($model, $fluid);
        else if($style == "plaintext")
            $view = new PlaintextView($model);
        else if(preg_replace("/[0-9]+/", "", $style) == "title")
        {
            $level = intval(str_replace("title", "", $style));
            $view = new TitleView($model, $level);
        }
        else
            throw("unknown style '$style'");
        parent::__construct($view);
    }
}
?>
