<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
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
     *
     * @param string $mode
     *  Specifies the insert mode (either 'css', 'asset', or 'static').
     */
    private function output_assets($mode)
    {
        $title = array(
            "css" => "User-defined CSS Files",
            "asset" => "Assets on the Server",
            "static" => "Satic Data Files"
        );
        $del_target = "";
        if($this->model->can_delete_asset())
            $del_target = $this->model->get_link_url("assetDelete", array(
                "file" => ":did",
                "mode" => $mode,
            ));
        $add_target = "";
        if($this->model->can_insert_asset())
            $add_target = $this->model->get_link_url("assetInsert",
                array('mode' => $mode));
        $items = array();
        $files = $this->model->get_asset_files($mode);
        $folder_files = array();
        $assets_without_folder = array();
        $assets_with_folder = array();
        foreach ($files as $key => $value) {
            if(!isset($folder_files[$value['folder']])){
                $folder_files[$value['folder']] = array();
            }
            $folder_files[$value['folder']][] = $value;
        }
        foreach ($folder_files as $key => $value) {
            if ($key == 'no_folder') {
                $assets_without_folder[] = new BaseStyleComponent("sortableList", array(
                    "items" => $value,
                    "is_editable" => true,
                    "url_delete" => $del_target,
                    "url_add" => $add_target,
                    "css" => "mb-3",
                ));
            } else {
                $assets_with_folder[] = new BaseStyleComponent("card", array(
                    "css" => "mb-3",
                    "title" => $key,
                    "type" => "light",
                    "is_expanded" => false,
                    "is_collapsible" => true,
                    "children" => array("list" => new BaseStyleComponent("sortableList", array(
                        "items" => $value,
                        "is_editable" => true,
                        "url_delete" => $del_target,
                        "url_add" => $add_target,
                    )))
                ));
            }
        }
        $items =  array_merge($assets_without_folder, $assets_with_folder);
        $card = new BaseStyleComponent("card", array(
            "css" => "mb-3",
            "title" => $title[$mode],
            "type" => "light",
            "is_expanded" => false,
            "is_collapsible" => true,
            "children" => $items
        ));
        $card->output_content();
    }

    /* Public Methods *********************************************************/

    /**
     * Render the footer view.
     */
    public function output_content()
    {
        require __DIR__ . "/tpl_asset.php";
    }
	
	public function output_content_mobile()
    {
        echo 'mobile';
    }
}
?>
