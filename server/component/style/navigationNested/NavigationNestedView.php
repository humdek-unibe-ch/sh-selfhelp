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
                "is_expanded" => $this->model->get_db_field("is_expanded", true),
                "has_hierarchy" => $this->model->get_db_field("has_hierarchy", false),
                "search_text" => $this->model->get_db_field("search_text"),
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
