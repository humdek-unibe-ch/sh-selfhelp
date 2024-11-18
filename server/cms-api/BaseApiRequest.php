<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

/**
 * @brief Base class for handling API requests in the CMS
 * 
 * This abstract class provides core functionality for processing API requests,
 * including authorization, response handling, and access control.
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


    /**
     * @brief Constructs a new BaseApiRequest instance
     * 
     * @param object $services Service container with core dependencies
     * @param string $keyword Page keyword identifier
     */
    public function __construct($services, $keyword)
    {
        $this->services = $services;
        $this->router = $services->get_router();
        $this->db = $services->get_db();
        $this->acl = $services->get_acl();
        $this->nav = $services->get_nav();
        $this->parsedown = $services->get_parsedown();
        $this->user_input = $services->get_user_input();
        $this->keyword = $keyword;
        $this->response = new CmsApiResponse();
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
