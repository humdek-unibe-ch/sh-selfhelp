<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the user component.
 */
class UserInsertView extends BaseView
{
    /* Private Properties *****************************************************/

    private $selected_user;

    /* Constructors ***********************************************************/

    /**
     * The constructor. Here all the main style components are created.
     *
     * @param object $model
     *  The model instance of the user component.
     * @param object $controller
     *  The controller instance of the user component.
     */
    public function __construct($model, $controller)
    {
        parent::__construct($model, $controller);
        $this->add_local_component("select", new BaseStyleComponent("select",
            array(
                "name" => "user_groups[]",
                "is_multiple" => true,
                "items" => $this->model->get_group_options()
            )
        ));
    }

    /* Private Methods ********************************************************/

    private function output_group_selection()
    {
        $this->output_local_component("select");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the cms view.
     */
    public function output_content()
    {
        $action_url = $this->model->get_link_url("user");
        $cancel_url = $this->model->get_link_url("user");
        require __DIR__ . "/tpl_insert_user.php";
    }
}
?>
