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
    }

    /* Private Methods ********************************************************/

    /**
     * Render the asset list.
     */
    private function output_assets($mode)
    {
        $del_target = "";
        if($this->model->can_delete_asset())
            $del_target = $this->model->get_link_url("assetDelete",
                array("file" => ":did"));
        $add_target = "";
        if($this->model->can_insert_asset())
            $add_target = $this->model->get_link_url("assetInsert",
                array('mode' => $mode));
        $list = new BaseStyleComponent("sortableList", array(
            "items" => $this->model->get_asset_files($mode),
            "is_editable" => true,
            /* "url_delete" => $del_target, */
            "url_add" => $add_target,
        ));
        $list->output_content();
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
