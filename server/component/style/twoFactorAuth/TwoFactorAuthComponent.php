<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseComponent.php";
require_once __DIR__ . "/TwoFactorAuthView.php";
require_once __DIR__ . "/TwoFactorAuthModel.php";
require_once __DIR__ . "/TwoFactorAuthController.php";

/**
 * The two-factor authentication component.
 *
 * Handles the 2FA verification process with a 6-digit code input system.
 * Includes functionality for code verification and resending.
 */
class TwoFactorAuthComponent extends BaseComponent
{
    /* Constructors ***********************************************************/

    /**
     * The constructor creates instances of the TwoFactorAuthModel class,
     * TwoFactorAuthController class, and TwoFactorAuthView class.
     *
     * @param object $services
     *  The service handler instance which holds all services.
     * @param int $id
     *  The id of the section associated with this component.
     * @param array $params
     *  The list of get parameters to propagate.
     */
    public function __construct($services, $id, $params = array())
    {
        $model = new TwoFactorAuthModel($services, $id, $params);
        $controller = null;
        if (!$model->is_cms_page()) {
            $controller = new TwoFactorAuthController($model);
        }
        $view = new TwoFactorAuthView($model, $controller);
        parent::__construct($model, $view, $controller);
    }
}
?>
