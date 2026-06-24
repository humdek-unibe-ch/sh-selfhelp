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
        $user_id = $_SESSION['2fa_user']['id'];

        // Atomically claim the code: flip is_used 0 -> 1 in a single UPDATE so
        // that if the same code is submitted twice (e.g. a duplicate POST over
        // HTTP/2, which production enables) only one request can consume it.
        // This avoids the race where two requests both read the code as unused,
        // one logs in and the other "fails" and overwrites the session with a
        // logged-out state — which bounced the user back to the login page.
        $claim = "UPDATE users_2fa_codes
                     SET is_used = 1
                   WHERE id_users = :id_users
                     AND code = :code
                     AND expires_at > NOW()
                     AND is_used = 0";
        $affected = $this->db->execute_update_db($claim, array(
            ':id_users' => $user_id,
            ':code' => $code
        ));

        if ($affected && $affected > 0) {
            // This request consumed the code -> log the user in.
            $this->login->log_user($_SESSION['2fa_user']);
            unset($_SESSION['2fa_user']);
            return true;
        }

        // No row was claimed. Either the code is wrong/expired, or a concurrent
        // duplicate request already consumed this exact (still valid) code. In
        // the latter case log this request in too, so a duplicate submit ends up
        // authenticated instead of failing and being redirected to login.
        $already_used = $this->db->query_db_first(
            "SELECT id FROM users_2fa_codes
              WHERE id_users = :id_users
                AND code = :code
                AND is_used = 1
                AND expires_at > NOW()
              ORDER BY created_at DESC
              LIMIT 1",
            array(':id_users' => $user_id, ':code' => $code)
        );
        if (!empty($already_used)) {
            $this->login->log_user($_SESSION['2fa_user']);
            unset($_SESSION['2fa_user']);
            return true;
        }

        return false;
    }

    /**
     * A wrapper function for the login service to check whether the current
     * session already belongs to a logged-in user.
     *
     * @return bool
     *  True if the user is logged in, false otherwise.
     */
    public function is_logged_in()
    {
        return $this->login->is_logged_in();
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
