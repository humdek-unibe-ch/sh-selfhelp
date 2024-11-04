<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/../BaseApiRequest.php";

/**
 * 
 * class is used for AJAX calls.
 */
class NavCmsApi extends BaseApiRequest
{
    /**
     * The constructor.
     *
     * @param object $services
     *  The service handler instance which holds all services
     */
    public function __construct($services, $keyword)
    {
        parent::__construct($services, $keyword);
    }

    /* Public Methods *********************************************************/

    public function GET_pages($mode)
    {
        $sql = "CALL get_user_acl(:uid, -1)";
        $params = array(':uid' => $_SESSION['id_user']);
        $all_pages = $this->db->query_db($sql, $params);
        // remove `web` or `mobile`
        $remove_type = $mode == pageAccessTypes_mobile ? pageAccessTypes_web : pageAccessTypes_mobile;
        $remove_type_id = $this->db->get_lookup_id_by_code(pageAccessTypes, $remove_type);
        $pages = array_values(array_filter($all_pages, function ($item) use ($remove_type_id) {
            return $item['id_pageAccessTypes'] != $remove_type_id;
        }));
        return $pages;
    }
}
?>
