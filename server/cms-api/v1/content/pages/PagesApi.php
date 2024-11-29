<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

use Swaggest\JsonSchema\Schema;

require_once __DIR__ . "/../../BaseApiRequest.php";
require_once __DIR__ . "/../../../../service/ext/swaggest_json_schema_0.12.39.0_require/vendor/autoload.php";

/**
 * @class PageCmsApi
 * @brief API handler for web page CMS operations
 * @extends BaseApiRequest
 * 
 * This class handles AJAX calls related to web page content management operations.
 * It provides endpoints for retrieving and managing web page content through the CMS.
 */
class PagesApi extends BaseApiRequest
{
    /**
     * @brief Constructor for WebPageCmsApi
     * 
     * @param object $services The service handler instance which holds all services
     * @param string $keyword The keyword identifier for the page
     */
    public function __construct($services, $keyword)
    {
        parent::__construct(services: $services, keyword: $keyword);
    }

    /* Public Methods *********************************************************/

    /**
     * @brief Gets the mobile-optimized content for a specific page
     *
     * @param string $keyword The unique identifier/slug for the page
     * 
     * @return array The mobile-formatted page content
     * 
     * @throws Exception with code 403 If user doesn't have permission to access the page
     * @throws Exception with code 404 If the requested page doesn't exist
     * 
     * @details
     * This method performs the following steps:
     * 1. Validates user access permissions for the requested page
     * 2. Checks if the page route exists
     * 3. Initializes a SectionPage instance with the provided keyword
     * 4. Returns the mobile-optimized content for the page
     */
    public function GET_page($keyword): array|null
    {
        if (!$this->check_page_access(keyword: $keyword)) {
            $this->error_response(
                error: "No access to page with keyword '{$keyword}'",
                status: 403
            );
        }

        if ($this->router->has_route($keyword)) {
            $page = new SectionPage(
                services: $this->services,
                keyword: $keyword,
                params: $this->router->route['params']
            );

            return $page->output_base_content_mobile();
        }

        $this->error_response(
            error: "Page with keyword '{$keyword}' not found",
            status: 404
        );
        return null;
    }

    public function PUT_page($keyword): array|null
    {
        if (!$this->check_page_access(keyword: $keyword, access_type: INSERT)) {
            $this->error_response(
                error: "No access to add data in page with keyword '{$keyword}'",
                status: 403
            );
        }

        if ($this->router->has_route($keyword)) {
            $page = new SectionPage(
                services: $this->services,
                keyword: $keyword,
                params: $this->router->route['params']
            );

            return $page->output_base_content_mobile();
        }

        $this->error_response(
            error: "Page with keyword '{$keyword}' not found",
            status: 404
        );
        return null;
    }
}
?>
