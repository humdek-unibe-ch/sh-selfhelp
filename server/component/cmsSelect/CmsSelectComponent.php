<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../cms/CmsView.php";
require_once __DIR__ . "/CmsSelectController.php";
require_once __DIR__ . "/../cms/CmsModel.php";
require_once __DIR__ . "/../cms/CmsComponent.php";

/**
 * The cms Select component.
 */
class CmsSelectComponent extends CmsComponent
{
    /* Private Properties *****************************************************/

    /**
     * The instance of the access control layer (ACL).
     */
    private $acl;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CmsModel class, the CmsView
     * class, and the CmsController class and passes the view instance to the
     * constructor of the parent class.
     *
     * @param array $services
     *  An associative array holding the differnt available services. See the
     *  class definition BasePage for a list of all services.
     * @param array $params
     *  The get parameters passed by the url with the following keys:
     *   - 'pid':     The id of the page that is currently edited.
     *   - 'sid':     The root id of a page or the section that is currently
     *                selected.
     *   - 'ssid':    The id of the section that is currently selected
     *                (only relevant for navigation pages).
     *   - 'did':     The id of a section to be deleted (only relevant in delete
     *                mode).
     *   - 'type':    This describes the database relation in order to know wheter
     *                to access pages, sections, navigations.
     */
    public function __construct($services, $params)
    {
        $this->acl = $services->get_acl();
        $model = new CmsModel($services, $params, "select");
        $controller = new CmsSelectController($model);
        $model->update_select_properties();
        $view = new CmsView($model);
        parent::__construct($model, $view, $controller);
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function to deny access on invalid pages and
     * sections.
     *
     * @retval bool
     *  True if the user has select access to page, false otherwise
     */
    public function has_access($skip_ids = false)
    {
        $pid = $this->model->get_active_page_id();
        if($pid != null &&
            !$this->acl->has_access_select($_SESSION['id_user'], $pid))
            return false;
        return parent::has_access();
    }
}
?>
