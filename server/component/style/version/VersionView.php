<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleView.php";

/**
 * The view class of the asset select component.
 */
class VersionView extends StyleView
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
     *  Specifies the insert mode (either 'css' or 'asset').
     */
    private function output($mode)
    {
        echo $mode;
    }

    /* Public Methods *********************************************************/

    /**
     * Render version view.
     */
    public function output_content()
    {
        $versionCard = new BaseStyleComponent("card", array(
            "css" => $this->css,
            "is_expanded" => false,
            "is_collapsible" => false,
            "children" => array(
                new BaseStyleComponent("template", array(
                    "path" => __DIR__ . "/tpl_version.php",
                    "items" => array(
                        "version_selfhelp" => "v3.1.1",
                        "version_db" => "v3.3.0",
                    ),
                ))
            )
        ));
        $versionCard->output_content();
    }
}
?>
