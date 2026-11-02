<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseController.php";

/**
 * The controller of the field version history: restores a version. The
 * component creates it only when the user has access to the field.
 */
class CmsFieldVersionsController extends BaseController
{
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_version'])) {
            $version = $model->restore(intval($_POST['restore_version']));
            if ($version) {
                $_SESSION[CONTROLLER_SUCCESS] = true;
                $_SESSION[CONTROLLER_SUCCESS_MSGS][] = 'Version ' . intval($version['version_no'])
                    . ' is the current content again.';
            } else {
                $_SESSION[CONTROLLER_FAIL] = true;
                $_SESSION[CONTROLLER_ERROR_MSGS][] = 'The version could not be restored.';
            }
            // Redirect so that a reload does not post the restore again.
            header("Location: " . $model->get_version_url());
            exit;
        }
    }
}
?>
