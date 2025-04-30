<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../StyleModel.php";

/**
 * The model class for the two-factor authentication component.
 */
class TwoFactorAuthModel extends StyleModel
{
    /* Private Properties *****************************************************/

    /**
     * The verification code length.
     */
    private $code_length = 6;

    /* Constructors ***********************************************************/

    /**
     * The constructor creates an instance of the TwoFactorAuthModel class.
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
        parent::__construct($services, $id, $params);
    }

    /* Public Methods *********************************************************/

    /**
     * Get the style name for this component.
     *
     * @return string
     *  The name of the style.
     */
    public function get_style_name()
    {
        return "twoFactorAuth";
    }

    /**
     * Get the verification code length.
     *
     * @return int
     *  The length of the verification code.
     */
    public function get_code_length()
    {
        return $this->code_length;
    }

    /**
     * Verify a 2FA code.
     *
     * @param string $code
     *  The code to verify.
     * @return bool
     *  True if the code is valid, false otherwise.
     */
    public function verify_code($code)
    {
        // TODO: Implement actual code verification
        return strlen($code) === $this->code_length;
    }

    /**
     * Resend a 2FA code.
     *
     * @return bool
     *  True if the code was sent successfully, false otherwise.
     */
    public function resend_code()
    {
        // TODO: Implement actual code resending
        return true;
    }
}
?>
