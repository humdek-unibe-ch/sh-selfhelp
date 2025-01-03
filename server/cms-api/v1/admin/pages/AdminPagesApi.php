<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

use Swaggest\JsonSchema\Schema;

require_once __DIR__ . "/../../BaseApiRequest.php";

/**
 * @class PageCmsApi
 * @brief API handler for web page CMS operations
 * @extends BaseApiRequest
 * 
 * This class handles AJAX calls related to web page content management operations.
 * It provides endpoints for retrieving and managing web page content through the CMS.
 */
class AdminPagesApi extends BaseApiRequest
{
    /**
     * @brief Constructor for AdminPagesApi
     * 
     * @param object $services The service handler instance which holds all services
     * @param string $keyword The keyword identifier for the page
     */
    public function __construct($services, $keyword)
    {
        parent::__construct(services: $services, keyword: $keyword);
        $this->response = new CmsApiResponse();
    }

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
