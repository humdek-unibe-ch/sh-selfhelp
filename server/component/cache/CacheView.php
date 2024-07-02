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
        $this->output_controller_alerts_success();
        $cache_types = array(
            $this->model->get_db()->get_cache()::CACHE_ALL => "All",
            $this->model->get_db()->get_cache()::CACHE_TYPE_PAGES => "Pages",
            $this->model->get_db()->get_cache()::CACHE_TYPE_SECTIONS => "Sections",
            $this->model->get_db()->get_cache()::CACHE_TYPE_FIELDS => "Fields",
            $this->model->get_db()->get_cache()::CACHE_TYPE_STYLES => "Styles",
            $this->model->get_db()->get_cache()::CACHE_TYPE_HOOKS => "Hooks",
            $this->model->get_db()->get_cache()::CACHE_TYPE_USER_INPUT => "User Input",
            $this->model->get_db()->get_cache()::CACHE_TYPE_CONDITION => "Conditions",
            $this->model->get_db()->get_cache()::CACHE_TYPE_LOOKUPS => "Lookups",
        );
        $cache_types_checkboxes = array();
        foreach ($cache_types as $key => $value) {
            $cache_types_checkboxes[] = new BaseStyleComponent("checkbox", array(
                "name" => $key,
                "label" => $value,
                "css" => "w-100 d-flex justify-content-between",
                "checkbox_value" => 1,
                "value" => $key == $this->model->get_db()->get_cache()::CACHE_ALL ? 1 : '',
                "toggle_switch" => 1,
            ));
        }


        $cache_holder = new BaseStyleComponent("div", array(
            "css" => 'container my-3',
            "children" => array(new BaseStyleComponent("card", array(
                "css" => "mb-3",
                "title" => 'Clear cache',
                "type" => "light",
                "is_expanded" => true,
                "is_collapsible" => false,
                "children" => array(
                    new BaseStyleComponent("form", array(
                        "label" => "Clear",
                        "type" => "danger",
                        "url" => "#",
                        "children" => $cache_types_checkboxes
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
