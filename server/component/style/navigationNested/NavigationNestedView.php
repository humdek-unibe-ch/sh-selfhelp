<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../navigation/NavigationView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the navigation nested component.
 */
class NavigationNestedView extends NavigationView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $this->add_local_component("nav",
            new BaseStyleComponent("nestedList", array(
                "items" => $this->model->get_navigation_items(),
                "id_prefix" => "navigation",
                "id_active" => $this->model->get_current_id(),
                "css" => $this->model->get_db_field("css_nav"),
                "is_expanded" => $this->model->get_db_field("is_expanded", true),
                "is_collapsible" => $this->model->get_db_field("is_collapsible", false),
                "search_text" => $this->model->get_db_field("search_text"),
                "title_prefix" => $this->model->get_db_field("title_prefix"),

            ))
        );
    }

    /* Private Methods ********************************************************/

    /**
     * Render the session navigation component.
     */
    protected function output_nav()
    {
        $this->output_local_component("nav");
    }
}
?>
