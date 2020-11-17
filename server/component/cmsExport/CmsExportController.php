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
class CmsExportController extends BaseController
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
        if($model->id > 0 && $model->export_json()){
            $this->success = true;
            $this->success_msgs[] = "Sucessfully exported file: " . $model->json['file_name'] . '.json';
        }else{
            $this->fail = true;
            $this->error_msgs[] = $model->json;
        }
    }

    /* Public Methods *********************************************************/
}
?>
