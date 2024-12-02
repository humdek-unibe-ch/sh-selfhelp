<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php

require_once __DIR__ . "/../../BaseApiRequest.php";

/**
 * @class NavigationApi
 * @brief API class for handling navigation-related CMS operations
 * @extends BaseApiRequest
 * 
 * This class provides functionality for handling AJAX calls related to
 * navigation and page management in the CMS system.
 */
class NavigationApi extends BaseApiRequest
{
    /**
     * @brief Constructor for NavigationApi class
     * 
     * @param object $services The service handler instance which holds all services
     * @param string $keyword Keyword parameter for the API request
     */
    public function __construct($services, $keyword)
    {
        parent::__construct(services: $services, keyword: $keyword);
    }

    /* Public Methods *********************************************************/

    /**
     * @brief Retrieves pages based on user access control and specified mode
     * 
     * @param string $mode The mode to filter pages (web/mobile)
     * @return array Array of pages filtered by access type
     * 
     * @details
     * This method performs the following operations:
     * - Retrieves user ACL through a stored procedure
     * - Filters pages based on the specified mode (web/mobile)
     * - Removes pages of the opposite access type
     * 
     * @note Currently includes commented-out schema validation code
     */
    public function GET_all_routes($mode): array
    {
        $sql = "CALL get_user_acl(:uid, -1)";
        $params = array(':uid' => $this->get_user_id());
        $all_pages = $this->db->query_db($sql, $params);
        // remove web or mobile
        $remove_type = $mode == pageAccessTypes_mobile ? pageAccessTypes_web : pageAccessTypes_mobile;
        $remove_type_id = $this->db->get_lookup_id_by_code(pageAccessTypes, $remove_type);
        $pages = array_values(array: array_filter(array: $all_pages, callback: function ($item) use ($remove_type_id): bool {
            // id_type: 2 = core, 3 = experiment, 4 = open
            return $item['id_pageAccessTypes'] != $remove_type_id && 
            $item['acl_select'] == 1 &&  
            $item['id_actions'] == 3 &&
            in_array($item['id_type'], ['2', '3', '4']);
        }));
        return $pages;
    }
}
?>
