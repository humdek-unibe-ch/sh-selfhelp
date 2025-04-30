<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../../BaseController.php";

/**
 * The controller class of the two-factor authentication component.
 * Handles 2FA code verification and resend functionality.
 */
class TwoFactorAuthController extends BaseController
{
    /**
     * Verification status
     */
    private $failed;
    private $resent;

    /**
     * Constructor for the TwoFactorAuthController.
     *
     * @param object $model
     *  The model instance.
     */
    public function __construct($model)
    {
        parent::__construct($model);

        // if 2fa_user is not set in the session redirect to login
        if (!isset($_SESSION['2fa_user'])) {
            header('Location: ' . $model->get_link_url(SH_LOGIN));
            exit;
        }

        // Check for verification_failed in URL parameters
        $this->fail = isset($_GET['verification_failed']);
        $this->error_msgs[] = $this->model->get_db_field('alert_fail', 'Invalid verification code. Please try again.');
        $this->resent = false;

        // Handle verification request
        if (isset($_POST['type']) && $_POST['type'] == '2fa_verify') {
            $code = '';
            for ($i = 1; $i <= 6; $i++) {
                if (isset($_POST['digit_' . $i])) {
                    $code .= $_POST['digit_' . $i];
                }
            }
            
            if (strlen($code) === 6 && $model->verify_2fa_code($code)) {
                header('Location: ' . $model->get_target_url());
                exit;
            } else {
                $this->failed = true;
                
                // Check if verification_failed parameter already exists
                $redirectUrl = $_SERVER['REQUEST_URI'];
                if (strpos($redirectUrl, 'verification_failed=') === false) {
                    // Add the parameter only if it doesn't exist
                    $redirectUrl .= (strpos($redirectUrl, '?') ? '&' : '?') . 'verification_failed=1';
                }
                
                header('Location: ' . $redirectUrl);
                exit;
            }
        }

        // Handle resend request
        if (isset($_POST['type']) && $_POST['type'] == '2fa_resend') {
            $this->resent = $model->resend_2fa_code();
        }
    }

    /**
     * Returns the verification failure status.
     *
     * @retval bool
     *  true if the verification has failed, false otherwise.
     */
    public function has_verification_failed()
    {
        return $this->failed;
    }

    /**
     * Returns the code resend status.
     *
     * @retval bool
     *  true if the code was resent successfully, false otherwise.
     */
    public function was_code_resent()
    {
        return $this->resent;
    }
}
?>
