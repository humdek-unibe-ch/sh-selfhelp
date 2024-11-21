<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * @class BaseApiRequest
 * @brief Base class for all API request handlers
 * 
 * This class provides common functionality and properties for all API requests,
 * including client type detection and service access.
 */
abstract class BaseApiRequest
{
    protected $router;
    protected $db;
    protected $acl;
    protected $login;
    protected $nav;
    protected $parsedown;
    protected $user_input;
    protected $keyword;
    protected CmsApiResponse $response;

    protected $services;

    private $debug_start_time;

    protected $client_type;

    /**
     * @brief Constructor for BaseApiRequest
     * 
     * @param object $services The service handler instance
     * @param string $keyword Keyword parameter for the API request
     */
    public function __construct($services, $keyword, $client_type = pageAccessTypes_web)
    {
        $this->services = $services;
        $this->router = $services->get_router();
        $this->db = $services->get_db();
        $this->acl = $services->get_acl();
        $this->nav = $services->get_nav();
        $this->parsedown = $services->get_parsedown();
        $this->user_input = $services->get_user_input();
        $this->login = $services->get_login();
        $this->keyword = $keyword;
        $this->response = new CmsApiResponse();
        $this->debug_start_time = microtime(true);
        $this->client_type = $client_type;
    }


    /**
     * @brief Check if request is from mobile app
     * 
     * @return bool true if request is from mobile app
     */
    protected function isAppRequest(): bool
    {
        return $this->client_type === pageAccessTypes_mobile;
    }

    /**
     * @brief Check if request is from web frontend
     * 
     * @return bool true if request is from web frontend
     */
    protected function isWebRequest(): bool
    {
        return $this->client_type === pageAccessTypes_web;
    }

    /**
     * @brief Checks if current user has access to the requested page
     * 
     * @return bool True if user has access, false otherwise
     * @private
     */
    private function has_access()
    {
        $page_id = $this->db->fetch_page_id_by_keyword($this->keyword);
        return $this->acl->has_access($_SESSION['id_user'], $page_id, 'select');
    }

    /**
     * @brief Authorizes the current user for page access
     * 
     * @return bool True if authorized, exits with 401 response if unauthorized
     */
    public function authorizeUser()
    {
        if (!$this->has_access()) {
            $this->response = new CmsApiResponse(401);
            $this->response->send();
            exit; // Add this line to halt further execution
        }
        return true;
    }

    /**
     * @brief Initializes the API response object
     * 
     * @param CmsApiResponse $response The response object to initialize
     */
    public function init_response($response)
    {
        $this->response = $response;
    }

    /**
     * @brief Retrieves the current response as an array
     * 
     * @return array The response data
     */
    public function get_response(): array
    {
        return $this->response->toArray();
    }

    /**
     * @brief Sets the HTTP status code for the response
     * 
     * @param int $status The HTTP status code
     */
    public function set_status(int $status): void
    {
        $this->response = new CmsApiResponse($status, $this->response->toArray()['data']);
    }

    /**
     * @brief Sets an error message in the response
     * 
     * @param string $error_message The error message to set
     */
    public function set_error_message(string $error_message): void
    {
        $this->response = new CmsApiResponse(
            $this->response->toArray()['status'],
            $this->response->toArray()['data'],
            $error_message
        );
    }

    /**
     * @brief Sends the response and terminates execution
     */
    public function return_response(): void
    {
        $this->response->send();
        exit;
    }

    /**
     * @brief Checks if the current user has access to a specific page
     * 
     * @param string $keyword The page keyword to check access for
     * @param string $accessType The type of access to check ('select', 'insert', 'update', 'delete')
     * @return bool True if user has access, false otherwise
     */
    public function checkPageAccess($keyword, $accessType = 'select')
    {
        // Get page ID from keyword
        $pageId = $this->db->fetch_page_id_by_keyword($keyword);

        if (!$pageId) {
            return false;
        }

        // Check access using ACL
        return $this->acl->has_access(
            $_SESSION['id_user'],
            $pageId,
            $accessType
        );
    }

    /**
     * @brief Sends an API response with optional data and status
     * 
     * @param mixed|null $data The response data
     * @param int $status HTTP status code (default: 200)
     * @param string|null $error Optional error message
     * @protected
     */
    protected function send_response($data = null, int $status = 200, ?string $error = null)
    {
        $this->response = new CmsApiResponse($status, $data, $error);
        $debug_start_time = $this->debug_start_time;
        $router = $this->services->get_router();
        // Add the logging callback
        $this->response->addAfterSendCallback(callback: function () use ($router, $debug_start_time): void {
            $router->log_user_activity($debug_start_time, true);
        });
        $this->response->send();
    }

    /**
     * @brief Sends an error response
     * 
     * @param string $error The error message
     * @param int $status HTTP status code (default: 400)
     * @protected
     */
    protected function error_response($error, int $status = 400)
    {
        $this->send_response(data: null, status: $status, error: $error);
    }

    /**
     * @brief Sends a success response
     * 
     * @param mixed|null $data The response data
     * @param int $status HTTP status code (default: 200)
     * @protected
     */
    protected function success_response($data = null, int $status = 200)
    {
        $this->send_response(data: $data, status: $status);
    }
}
?>
