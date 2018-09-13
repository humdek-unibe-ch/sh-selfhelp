<?php
require_once __DIR__ . "/../navigation/NavigationView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the navigation nested component.
 */
class NavigationNestedView extends NavigationView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'has_hierarchy' (false).
     * If set to true the nested list is collapsible via a chevron.
     * If set to false, the chevron is not rendered.
     */
    private $has_hierarchy;

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
        $this->has_hierarchy = $this->model->get_db_field("has_hierarchy", false);
        $this->add_local_component("nav",
            new BaseStyleComponent("nestedList", array(
                "items" => $this->model->get_navigation_items(),
                "id_prefix" => "navigation",
                "id_active" => $this->model->get_current_id(),
                "is_expanded" => true,
                "has_hierarchy" => $this->has_hierarchy,
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
