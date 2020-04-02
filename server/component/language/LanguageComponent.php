<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/LanguageView.php";
require_once __DIR__ . "/LanguageController.php";
require_once __DIR__ . "/LanguageModel.php";

/**
 * The languaege  component.
 */
class LanguageComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CMSPreferenceModel class,
     * the CMSPreferenceView class, and the CMSPreferenceControler class and passes
     * the view and controller instances to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     */
    public function __construct($services, $params)
    {
        $lid = isset($params['lid']) ? intval($params['lid']) : null;
        $model = new LanguageModel($services, $lid);
        $controller = new LanguageController($model, $lid);
        $view = new LanguageView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
