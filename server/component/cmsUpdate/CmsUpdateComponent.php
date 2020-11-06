<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseComponent.php";
require_once __DIR__ . "/../cms/CmsView.php";
require_once __DIR__ . "/../cms/CmsModel.php";
require_once __DIR__ . "/../cms/CmsComponent.php";
require_once __DIR__ . "/CmsUpdateController.php";
require_once __DIR__ . "/CmsUpdateView.php";

/**
 * The cms update component.
 */
class CmsUpdateComponent extends CmsComponent
{
    /* Private Properties *****************************************************/

    /**
     * The instance of the access control layer (ACL).
     */
    private $acl;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the CmsModel class, the CmsView
     * or the CmsUpdateView class (depending on the mode), and the
     * CmsUpdateController class and passes the view, controller, an d model
     * instance to the constructor of the parent class.
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
     *   - 'mode':    This describes the update mode which can have the values
     *                 - update: update the propertiy fields of a section or page.
     *                 - insert: add a new section to a section or a page.
     *                 - delete: remove a section from a section or a page.
     * @param number $id_cms_page
     *  The id of the current cms page being loaded
     */
    public function __construct($services, $params, $id_cms_page)
    {
        $this->acl = $services->get_acl();
        $model = new CmsModel($services, $params, "update", $id_cms_page);
        $controller = new CmsUpdateController($model);
        if($params["mode"] == "update")
        {
            $model->update_select_properties();
            $view = new CmsView($model, $controller);
        }
        else
        {
            $update_prop_method = "update_" . $params["mode"] . "_properties";
            $model->$update_prop_method();
            $type = isset($params["type"]) ? $params["type"] : null;
            $view = new CmsUpdateView($model, $controller, $params["mode"], $type);
        }
        parent::__construct($model, $view, $controller);
        $this->set_request_access($id_cms_page, "AjaxSearch",
            "search_data_source");
        $this->set_request_access($id_cms_page, "AjaxSearch",
            "search_anchor_section");
    }

    /* Public Methods *********************************************************/

    /**
     * Redefine the parent function to deny access on invalid pages and
     * sections.
     *
     * @retval bool
     *  True if the user has update access to page, false otherwise
     */
    public function has_access($skip_ids = false)
    {
        $pid = $this->model->get_active_page_id();
        if(!$this->acl->has_access_update($_SESSION['id_user'], $pid))
            return false;
        return parent::has_access();
    }
}
?>
