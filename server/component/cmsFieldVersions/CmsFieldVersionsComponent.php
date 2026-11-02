<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/CmsFieldVersionsView.php";
require_once __DIR__ . "/CmsFieldVersionsModel.php";
require_once __DIR__ . "/CmsFieldVersionsController.php";

/**
 * The version history page of a versioned CMS field: lists the versions,
 * shows a version with its changes compared to the current content and
 * restores it.
 */
class CmsFieldVersionsComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the Model, the Controller and the
     * View class and passes them to the constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the different available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The route parameters: relation, owner_id, id_fields, id_languages and
     *  the optional version (number) to show.
     */
    public function __construct($services, $params)
    {
        $model = new CmsFieldVersionsModel($services, $params);
        $controller = $model->has_access() ? new CmsFieldVersionsController($model) : null;
        $view = new CmsFieldVersionsView($model, $controller);
        parent::__construct($model, $view, $controller);
    }

    /* Public Methods *********************************************************/

    /**
     * The field must be versioned and the user needs update access to the
     * page that owns it.
     *
     * @retval bool
     *  True if the user may see the history.
     */
    public function has_access()
    {
        return $this->model->has_access();
    }
}
?>
