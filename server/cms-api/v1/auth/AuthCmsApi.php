<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../BaseApiRequest.php";
require_once __DIR__ . "/../CmsApiResponse.php";
require_once __DIR__ . "/../../../service/globals.php";

/**
 * @class AuthCmsApi
 * @brief API class for handling user authentication
 * @extends BaseApiRequest
 * 
 * This class provides functionality for user login, supporting both
 * regular email-based and anonymous username-based authentication.
 */
class AuthCmsApi extends BaseApiRequest
{

    /**
     * @brief Constructor for AuthCmsApi class
     * 
     * @param object $services The service handler instance which holds all services
     * @param string $keyword The keyword identifier for the page (not used)
     */
    public function __construct($services, $keyword)
    {
        parent::__construct(services: $services, keyword: $keyword);
    }

    /**
     * @brief Handles user login request
     * 
     * @param string $password The password string entered by the user
     * @param string $user The username or email address entered by the user
     */
    public function POST_login($password, $user): void
    {
        if (!$password || !$user) {
            $this->error_response("Required credentials are missing", 400);
            return;
        }

        // Check if user is already logged in
        if ($this->login->is_logged_in()) {
            $this->login->logout(); // Logout existing session
        }

        if (!$password) {
            $this->error_response("Password is required", 400);
            return;
        }

        $success = false;
        $message = "";

        // Handle anonymous users login (username-based)
        if ($this->db->is_anonymous_users()) {
            if (!$user) {
                $this->error_response("Username is required", 400);
                return;
            }

            $success = $this->login->check_credentials_user_name($user, $password);
            $message = $success ? "Login successful" : "Invalid username or password";
        }
        // Handle regular login (email-based)
        else {
            if (!$user) {
                $this->error_response("Email is required", 400);
                return;
            }

            $success = $this->login->check_credentials($user, $password);
            $message = $success ? "Login successful" : "Invalid email or password";
        }

        // Prepare response data
        if ($success) {
            $responseData = [
                'target_url' => $_SESSION['target_url'],
                'user_id' => $_SESSION['id_user']
            ];
            $this->success_response($responseData);
        } else {
            $this->error_response($message, 401);
        }
    }
}
?>
