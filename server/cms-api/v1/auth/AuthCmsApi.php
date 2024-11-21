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
     * @return CmsApiResponse Response object containing login result
     */
    public function POST_login($password, $user): CmsApiResponse
    {

        if (!$password || !$user) {
            return new CmsApiResponse(status: 400, data: null, error: "Required credentials are missing");
        }

        // Check if user is already logged in
        if ($this->login->is_logged_in()) {
            $this->login->logout(); // Logout existing session
        }

        if (!$password) {
            return new CmsApiResponse(status: 400, data: null, error: "Password is required");
        }

        $success = false;
        $message = "";

        // Handle anonymous users login (username-based)
        if ($this->db->is_anonymous_users()) {

            if (!$user) {
                return new CmsApiResponse(status: 400, data: null, error: "Username is required");
            }

            $success = $this->login->check_credentials_user_name($user, $password);
            $message = $success ? "Login successful" : "Invalid username or password";
        }
        // Handle regular login (email-based)
        else {

            if (!$user) {
                return new CmsApiResponse(status: 400, data: null, error: "Email is required");
            }

            $success = $this->login->check_credentials($user, $password);
            $message = $success ? "Login successful" : "Invalid email or password";
        }

        // Prepare response data
        $responseData = null;
        if ($success) {
            $responseData = [
                'target_url' => $this->login->get_last_url(),
                'user_id' => $this->login->get_user_id()
            ];
        }

        return new CmsApiResponse(
            status: $success ? 200 : 401,
            data: $responseData,
            error: $message
        );
    }
}
?>
