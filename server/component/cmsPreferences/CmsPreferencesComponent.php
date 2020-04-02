<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/CmsPreferencesView.php";
require_once __DIR__ . "/CmsPreferencesModel.php";

/**
 * The class to define the asset select component.
 */
class CmsPreferencesComponent extends BaseComponent
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
        $view = new CmsPreferencesView($model);
        parent::__construct($model, $view);
    }
}
?>
