<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";
/**
 * The controller class of the style component.
 */
class ClockworkController extends BaseController
{
    /* Private Properties *****************************************************/


    /* Constructors ***********************************************************/

    /**
     * The constructor.
     *
     * @param object $model
     *  The model instance of the login component.
     */
    public function __construct($model)
    {
        parent::__construct($model);
        if (!$model->is_clockwork_enabled()) {
            $this->fail = true;
            $this->error_msgs[] = "Clockwork is not enabled";
            return;
        } else {
            if (isset($_GET['request'])) {                
                return $model->handleMetadata();
            }
        }
    }

    /* Private Methods ********************************************************/
}
?>
