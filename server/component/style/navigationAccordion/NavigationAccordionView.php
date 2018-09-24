<?php
require_once __DIR__ . "/../navigation/NavigationView.php";
require_once __DIR__ . "/../BaseStyleComponent.php";

/**
 * The view class of the navigation accordion component.
 */
class NavigationAccordionView extends NavigationView
{
    /* Private Properties *****************************************************/

    /**
     * DB field 'title_prefix' (empty string).
     * A prefix that will be appended to the title of each root item. This is
     * omitted if the field is not set.
     */
    private $title_prefix;

    /**
     * DB style field 'label_root' ("Intro").
     * The label of the root navigation item.
     */
    private $root_name;

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
        $this->root_name = $this->model->get_db_field("label_root", "Intro");
        $this->title_prefix = $this->model->get_db_field("title_prefix");
        $this->add_local_component("nav",
            new BaseStyleComponent("accordionList", array(
                "items" => $this->model->get_navigation_items(),
                "title_prefix" => $this->title_prefix,
                "id_active" => $this->model->get_current_id(),
                "is_expanded" => false,
                "label_root" => $this->root_name,
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
