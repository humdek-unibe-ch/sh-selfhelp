<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../cmsPreferences/CmsPreferencesView.php";
require_once __DIR__ . "/../cmsPreferences/CmsPreferencesModel.php";
require_once __DIR__ . "/../cmsPreferences/CmsPreferencesController.php";

/**
 * The class to define the asset select component.
 */
class CmsPreferencesUpdateComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model class and the View
     * class and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services)
    {
        $model = new CmsPreferencesModel($services);
        $controller = new CmsPreferencesController($model);
        $view = new CmsPreferencesView($model, $controller, "edit");        
        parent::__construct($model, $view, $controller);
    }
}
?>
