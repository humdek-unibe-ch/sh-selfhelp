<?php
require_once __DIR__ . "/../BaseView.php";
require_once __DIR__ . "/../style/BaseStyleComponent.php";

/**
 * The view class of the asset select component.
 */
class AssetSelectView extends BaseView
{
    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $del_target = "";
        if($this->model->can_delete_asset())
            $del_target = $this->model->get_link_url("assetDelete",
                array("file" => ":did"));
        $add_target = "";
        if($this->model->can_insert_asset())
            $add_target = $this->model->get_link_url("assetInsert");

        $this->add_local_component("asset-list",
            new BaseStyleComponent("sortableList", array(
                "items" => $this->model->get_asset_files(),
                "is_editable" => true,
                /* "delete_target" => $del_target, */
                "insert_target" => $add_target,
            ))
        );
    }

    /* Private Methods ********************************************************/

    private function output_assets()
    {
        $this->output_local_component("asset-list");
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_asset.php";
    }
}
?>
