<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../BaseApiRequest.php";
require_once __DIR__ . "/pages/AdminPagesApi.php";
require_once __DIR__ . "/pages/AdminPageDetailApi.php";

/**
 * @class AdminCmsApi
 * @brief Main API handler for CMS administrative operations
 * @extends BaseApiRequest
 * 
 * This class serves as the primary entry point for all administrative CMS operations.
 * It manages access to various administrative functionalities including page management,
 * user management, and other administrative tasks.
 * 
 * Security Note:
 * All endpoints in this class require authenticated access. Unauthorized access
 * attempts will be rejected with appropriate error responses.
 */
class AdminCmsApi extends BaseApiRequest
{
    /**
     * @brief Constructor for AdminCmsApi
     * 
     * @param object $services Service container providing access to system services
     * @param string $keyword Keyword parameter for API request identification
     * 
     * @details
     * Initializes the admin API with required services and sets the client type
     * to web interface access.
     */
    public function __construct($services, $keyword)
    {
        parent::__construct(services: $services, keyword: $keyword);
        $this->client_type = pageAccessTypes_web;
    }

    /* Public Methods *********************************************************/

    public function GET_access(): void
    {
        if (!$this->response->is_logged_in()) {
            $this->error_response(
                error: "User is not allowed to access this page",
                status: 403
            );
            $this->response->set_data(array("access" => false));
        } else {
            $this->response->set_data(array("access" => true));
        }
    }

    /**
     * @brief Retrieves all accessible pages for the authenticated administrator
     * 
     * @return void Response is handled through CmsApiResponse
     * 
     * @details
     * This endpoint:
     * 1. Creates an instance of AdminPagesApi to handle page operations
     * 2. Retrieves pages based on user's access control level
     * 3. Returns the filtered list of pages through the response object
     * 
     * @note
     * - Requires authenticated access
     * - Returns 401 Unauthorized if user is not logged in
     * - Returns 200 OK with page data if successful
     */
    public function GET_pages(): void
    {
        $pages = new AdminPagesApi(services: $this->services, keyword: $this->keyword);
        $this->response->set_data($pages->GET_pages());
    }
    
    /**
     * @brief Retrieves all sections and content for a specific page by keyword
     * 
     * @param string $page_keyword The keyword of the page to retrieve sections for
     * @return void Response is handled through CmsApiResponse
     * 
     * @details
     * This endpoint:
     * 1. Creates an instance of AdminPagesApi to handle page operations
     * 2. Retrieves the complete page structure with all sections and fields
     * 3. Returns the structured JSON data through the response object
     * 
     * @note
     * - Requires authenticated access
     * - Returns 404 Not Found if page doesn't exist
     * - Returns 403 Forbidden if user doesn't have access to the page
     * - Returns 200 OK with page structure if successful
     */
    public function GET_page_fields($page_keyword): void
    {
        $pages_api = new AdminPageDetailApi(services: $this->services, keyword: $this->keyword);
        $this->response->set_data($pages_api->GET_page_fields($page_keyword));
    }
}
?>
