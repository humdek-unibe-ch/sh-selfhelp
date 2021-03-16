<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the group insert component.
 */
class CmsPreferencesController extends BaseController
{
    /* Private Properties *****************************************************/

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
        if (isset($_POST['default_language_id']) && isset($_POST['callback_api_key'])) {    
            $res = true;       
            foreach($this->model->get_db()->fetch_all_modules() as $module){
                $res = $res && $this->model->update_module_status($module['id'], isset($_POST[$module['module_name']]) ? 1 : 0) !== false;
            }
            if ($res && $this->model->update_cmsPreferences(array(
                'default_language_id' => $_POST['default_language_id'],
                'callback_api_key' => $_POST['callback_api_key'],
                'fcm_api_key' => $_POST['fcm_api_key'],
                'fcm_sender_id' => $_POST['fcm_sender_id'],
            )) !== false) {                
                $this->success = true;
                $this->model->pull_cmsPreferences();
                $this->success_msgs[] = "CMS Preferences were successfully updated";
            } else {
                $this->fail = true;
                $this->error_msgs[] = "Failed to update CMS Preferences";
            }
        }
    }

    /* Public Methods *********************************************************/
}
?>
