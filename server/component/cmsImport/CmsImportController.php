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
class CmsImportController extends BaseController
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
        if (isset($_POST['json'])) {
            if ($model->validate_and_set_json($_POST['json'])) {
                $this->success = true;
                $this->success_msgs[] = "Successfully parse JSON file: " . $model->json['file_name'] . '.json';
                if ($model->type == 'section') {
                    $res = $model->import_section(isset($_POST['parent_id']) ? $_POST['parent_id'] : null, isset($_POST['position']) ? $_POST['position'] : null);
                    if($res === true && false){
                        $this->success_msgs[] = "Section: " . $model->json['section']['section_name'] . ' was successfully imported.';
                    }else{
                        $this->fail = true;
                        $this->error_msgs[] = "Error! Section: " . $model->json['section']['section_name'] . ' was not imported';
                        $this->error_msgs[] = $res;
                    }
                }
                $model->get_services()->get_db()->clear_cache();
            } else {
                $this->fail = true;
                $this->error_msgs[] = $model->json;
            }
        }
    }

    /* Public Methods *********************************************************/
}
?>
