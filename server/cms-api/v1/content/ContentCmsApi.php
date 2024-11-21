<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../BaseApiRequest.php";
require_once __DIR__ . "/pages/PagesApi.php";
require_once __DIR__ . "/navigation/NavigationApi.php";

/**
 * @file ContentCmsApi.php
 * @brief API class for handling content delivery operations
 * 
 * This class serves as the main entry point for content delivery operations,
 * delegating specific tasks to specialized API classes. It handles both web
 * and mobile client requests, providing appropriate content formatting based
 * on the client type.
 * 
 * Features:
 * - Page content retrieval via PagesApi
 * - Navigation structure via NavigationApi
 * - Client-type aware content delivery (web/mobile)
 * 
 */
class ContentCmsApi extends BaseApiRequest
{
    /**
     * @brief Constructor for ContentCmsApi
     * 
     * Initializes the content delivery API with necessary services and client context.
     * 
     * @param object $services The service handler instance which holds all services
     * @param string $keyword The keyword identifier for the page
     * @param string $client_type The type of client (web/mobile) making the request
     */
    public function __construct($services, $keyword, $client_type)
    {
        parent::__construct(services: $services, keyword: $keyword, client_type: $client_type);
    }

    /* Public Methods *********************************************************/

    /**
     * @brief Retrieve page content by keyword
     * 
     * Delegates page content retrieval to the PagesApi class. The content
     * will be formatted according to the client type (web/mobile).
     * 
     * @param string $keyword The unique identifier for the requested page
     * @return array|null Array containing page content if found, null otherwise
     * 
     * @example
     * $content = $api->GET_page('homepage');
     */
    public function GET_page($keyword): array|null
    {
        $pages = new PagesApi(services: $this->services, keyword: $this->keyword);
        return $pages->GET_page(keyword: $keyword);
    }

    /**
     * @brief Retrieve all available navigation routes
     * 
     * Delegates navigation structure retrieval to the NavigationApi class.
     * Returns routes appropriate for the current client type.
     * 
     * @return array|null Array of available routes if found, null otherwise
     * 
     * @example
     * $routes = $api->GET_all_routes();
     * 
     * @note The returned structure varies between web and mobile clients
     * to optimize for each platform's needs.
     */
    public function GET_all_routes(): array|null
    {
        $navigation = new NavigationApi(services: $this->services, keyword: $this->keyword);
        return $navigation->GET_all_routes(mode: $this->client_type);
    }
}
?>
