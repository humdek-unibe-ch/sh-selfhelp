<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseAjax.php";

/**
 * Lightweight AJAX endpoint for polling refresh events.
 * Returns unconsumed refresh events for the current user,
 * including which section IDs should be refreshed, then marks them consumed.
 */
class AjaxRefreshEvents extends BaseAjax
{
    public function __construct($services)
    {
        parent::__construct($services);
    }

    /**
     * Check for pending refresh events.
     * The AjaxRequest framework wraps this in {"success": true, "data": ...}.
     *
     * @param array $data POST parameters
     * @return array Response data
     */
    public function check($data)
    {
        if (!isset($_SESSION['id_user'])) {
            return array('events' => array(), 'refresh_sections' => array());
        }

        $id_user = $_SESSION['id_user'];

        $events = $this->db->query_db(
            "SELECT re.id, re.event_type, re.event_data,
                    GROUP_CONCAT(res.id_sections) AS section_ids
             FROM refresh_events re
             LEFT JOIN refresh_events_sections res ON re.id = res.id_refresh_events
             WHERE re.id_users = :uid AND re.consumed = 0
             GROUP BY re.id
             ORDER BY re.created_at ASC",
            array(':uid' => $id_user)
        );

        $section_ids = array();
        $event_ids = array();
        foreach ($events as $event) {
            $event_ids[] = $event['id'];
            if (!empty($event['section_ids'])) {
                $ids = explode(',', $event['section_ids']);
                $section_ids = array_merge($section_ids, $ids);
            }
        }

        if (!empty($event_ids)) {
            $placeholders = implode(',', array_fill(0, count($event_ids), '?'));
            $this->db->query_db(
                "UPDATE refresh_events SET consumed = 1 WHERE id IN ($placeholders)",
                $event_ids
            );
        }

        $section_ids = array_unique(array_map('intval', $section_ids));

        return array(
            'events' => $events,
            'refresh_sections' => array_values($section_ids)
        );
    }
}
?>
