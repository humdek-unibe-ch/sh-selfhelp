<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/BaseStyleModel.php";

/**
 * The class to define the simple style component. A simple style component serves
 * to render content in different views. The views are specified by the style.
 *
 * In contrast to a BaseStyleComponent, the SimpleStyleComponent pass the already created model
 */
class SimpleStyleComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the SimpleStyleComponent class and the
     * a StyleView class according to the style parameter. It passes the view
     * instance to the constructor of the parent class.
     *
     * @param string $model
     *  The model of the component
    
     */
    public function __construct($model)
    {
        $className = ucfirst($model->get_style_name()) . "View";
        if(class_exists($className))
        {
            $view = new $className($model);
        }
        else
        {
            $model = new BaseStyleModel(array("style_name" => $model->get_style_name()),
                "unknownStyle");
            $view = new UnknownStyleView($model);
        }
        parent::__construct($model, $view);
    }
}
?>
