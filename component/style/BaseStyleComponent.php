<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/BaseStyleModel.php";

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
     * @param array $fields
     *  An array containing fields for the view to render to content. The
     *  required are dependent of the style. The array must contain key, value
     *  pairs where the key is the name of the field and the value the content
     *  of the field.
     * @param array $fields_full
     *  An array containing fields for the view to render to content. The
     *  required fields are dependent of the style. The array must contain key,
     *  value pairs where the key is the name of the field and the value an
     *  array with the following keys:
     *   'content': hodling the content of the field that will be rendered.
     *   'type':    the type of the field indication how to render the field.
     *   'id':      a unique numerical value, describing this field.
     */
    public function __construct($style, $fields, $fields_full=array())
    {
        $className = ucfirst($style) . "View";
        if(class_exists($className))
        {
            $this->model = new BaseStyleModel($fields);
            $this->model->set_fields_full($fields_full);
            $view = new $className($this->model);
        }
        else
        {
            $this->model = new BaseStyleModel(array("style_name" => $style));
            $view = new UnknownStyleView($this->model);
        }
        parent::__construct($view);
    }
}
?>
