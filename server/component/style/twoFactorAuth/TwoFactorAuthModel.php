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
     * Verify a 2FA code.
     *
     * @param string $code
     *  The code to verify.
     * @return bool
     *  True if the code is valid, false otherwise.
     */
    public function verify_2fa_code($code)
    {        
        return $this->login->verify_2fa_code($code);
    }

    /**
     * A wrapper function for the method Login::get_last_url of the login
     * service.
     */
    public function get_target_url()
    {
        return $this->login->get_target_url($this->router->generate('home'));
    }
}
?>
