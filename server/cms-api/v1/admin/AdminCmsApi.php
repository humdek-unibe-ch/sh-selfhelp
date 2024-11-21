<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

use Swaggest\JsonSchema\Schema;

require_once __DIR__ . "/../BaseApiRequest.php";
require_once __DIR__ . "/../../../service/ext/swaggest_json_schema_0.12.39.0_require/vendor/autoload.php";

/**
 * @class PageCmsApi
 * @brief API handler for web page CMS operations
 * @extends BaseApiRequest
 * 
 * This class handles AJAX calls related to web page content management operations.
 * It provides endpoints for retrieving and managing web page content through the CMS.
 */
class AdminCmsApi extends BaseApiRequest
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
        $this->client_type = pageAccessTypes_web;
    }

    /* Public Methods *********************************************************/
}
?>
