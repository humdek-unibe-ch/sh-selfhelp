<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the section navigation component.
 */
class NavSectionView extends BaseView
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
        $this->add_local_component("nav-section",
            new BaseStyleComponent("accordion_list"),
            array(
                "items" => $this->model->get_children(),
                "title_prefix" => $this->model->get_item_prefix(),
                "id_active" => $this->model->get_current_id(),
                "is_expanded" => false,
                "root_name" => "Intro"
            )
        );
    }


    /* Public Methods *********************************************************/

    /**
     * Render the login view.
     */
    public function output_content()
    {
        $this->output_local_component("nav-section");
    }
}
?>
