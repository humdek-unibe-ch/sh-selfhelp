<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseView.php";

/**
 * The view class of the asset select component.
 */
class CacheView extends BaseView
{

    /* Private Properties *****************************************************/

    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     * @param object $controller
     *  The controller instance of the component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
    }

    /* Private Methods ********************************************************/

    /* Public Methods *********************************************************/

    /**
     * Render the view.
     */
    public function output_content()
    {
        $cache_holder = new BaseStyleComponent("div", array(
            "css" => 'container my-3',
            "children" => array(new BaseStyleComponent("card", array(
                "css" => "mb-3",
                "title" => 'Clear cache',
                "type" => "light",
                "is_expanded" => true,
                "is_collapsible" => false,
                "children" => array(
                    new BaseStyleComponent("div", array(
                        "css" => "d-flex align-items-end",
                        "children" => array()
                    ))
                )
            )))
        ));
        $cache_holder->output_content();
    }

    public function output_content_mobile()
    {
        return;
    }
}
?>
