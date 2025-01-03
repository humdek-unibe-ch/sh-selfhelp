<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

use Swaggest\JsonSchema\Schema;

require_once __DIR__ . "/../../BaseApiRequest.php";

/**
 * @class AdminPagesApi
 * @brief API handler for administrative page management operations
 * @extends BaseApiRequest
 * 
 * This class provides functionality for managing pages through the CMS admin interface.
 * It handles operations such as retrieving, creating, updating, and managing page content
 * and metadata. All operations require administrative privileges.
 * 
 * Security Features:
 * - Authentication check on all endpoints
 * - ACL (Access Control List) validation
 * - Input validation and sanitization
 * 
 * @note
 * This API specifically handles experiment pages (id_type = 3) and enforces
 * proper access control through ACL checks.
 */
class AdminPagesApi extends BaseApiRequest
{
    /**
     * @brief Constructor for AdminPagesApi
     * 
     * @param object $services The service handler instance which holds all services
     * @param string $keyword The keyword identifier for the page
     * 
     * @details
     * Initializes the admin pages API with necessary services and sets up
     * the response handler for consistent API responses.
     */
    public function __construct($services, $keyword)
    {
        parent::__construct(services: $services, keyword: $keyword);
        $this->response = new CmsApiResponse();
    }

    /**
     * @brief Retrieves all experiment pages accessible to the authenticated user
     * 
     * @return array|CmsApiResponse Returns either:
     *         - array: List of accessible experiment pages if authentication successful
     *         - CmsApiResponse: Error response if authentication fails
     * 
     * @details
     * This method:
     * 1. Verifies user authentication
     * 2. Retrieves user's ACL permissions
     * 3. Filters pages based on:
     *    - Page type (experiment pages only)
     *    - User's access permissions (acl_select = 1)
     * 
     * @throws Exception If database query fails
     * 
     * @note
     * - Only returns experiment pages (id_type = 3)
     * - Requires user to have select permission (acl_select = 1)
     * - Returns 401 Unauthorized if user is not logged in
     */
    public function GET_pages(): array|CmsApiResponse
    {
        if (!$this->login->is_logged_in()) {
            $this->error_response(
                error: "User is not logged in",
                status: 401
            );            
            return null;
        }

        $sql = "CALL get_user_acl(:uid, -1)";
        $params = array(':uid' => $this->get_user_id());
        $all_pages = $this->db->query_db($sql, $params);
        // keep only experiment pages
        $pages = array_values(array: array_filter(array: $all_pages, callback: function ($item): bool {
            // id_type: 2 = core, 3 = experiment, 4 = open
            return $item['id_type'] == 3 &&
                $item['acl_select'] == 1;
        }));
        return $pages;
    }
}
