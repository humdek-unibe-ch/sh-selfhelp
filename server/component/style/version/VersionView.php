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

    /* Public Methods *********************************************************/

    /**
     * Render version view.
     */
    public function output_content()
    {        
        $db_version = $this->model->get_db_version();
        $app_version = rtrim(shell_exec("git describe --tags"));
        $plugins = $this->model->get_plugins();        
        $versionCard = new BaseStyleComponent("card", array(
            "css" => 'mb-3 '. $this->css,
            "is_expanded" => false,
            "is_collapsible" => false,
            "children" => array(
                new BaseStyleComponent("markdown", array(
                    "text_md" => "| SelfHelp | Version | License | Comments |
                                    |-|-|-|-|
                                    | Application | " . $app_version . "   | <a href='https://www.mozilla.org/en-US/MPL/2.0/'>MPL2.0</a> | |
                                    | Database | " . $db_version . "   | | |" . $plugins,
                ))
            )
        ));
        $versionCard->output_content();
    }	
}
?>
