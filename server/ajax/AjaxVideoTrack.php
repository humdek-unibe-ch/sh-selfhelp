<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
require_once __DIR__ . "/BaseAjax.php";

/**
 * AJAX endpoint for video watch-progress events.
 * Enriches the automatic user_activity log row via Router override.
 * Always attributes the row to the logged-in session user (never from POST).
 */
class AjaxVideoTrack extends BaseAjax
{
    private const ALLOWED_EVENTS = array('play', 'heartbeat', 'ended');

    public function __construct($services)
    {
        parent::__construct($services);
    }

    /**
     * Record a video watch event.
     *
     * @param array $data POST payload:
     *  - event: play|heartbeat|ended
     *  - section_id: int
     *  - page_keyword: string
     *  - currentTime: float seconds
     *  - duration: float seconds
     *  - percent: float 0-100
     *  - source: optional short filename
     * @return array|false
     */
    public function track($data)
    {
        if (!$this->login->is_logged_in() || empty($_SESSION['id_user'])) {
            $this->router->skip_next_activity_log();
            return false;
        }

        $event = isset($data['event']) ? strtolower(trim((string)$data['event'])) : '';
        if (!in_array($event, self::ALLOWED_EVENTS, true)) {
            $this->router->skip_next_activity_log();
            return false;
        }

        $section_id = isset($data['section_id']) ? intval($data['section_id']) : 0;
        if ($section_id <= 0) {
            $this->router->skip_next_activity_log();
            return false;
        }

        $page_keyword = isset($data['page_keyword']) ? trim((string)$data['page_keyword']) : '';
        if ($page_keyword === '' || !$this->db->fetch_page_id_by_keyword($page_keyword)) {
            $this->router->skip_next_activity_log();
            return false;
        }

        $current_time = isset($data['currentTime']) ? floatval($data['currentTime']) : 0.0;
        $duration = isset($data['duration']) ? floatval($data['duration']) : 0.0;
        $percent = isset($data['percent']) ? floatval($data['percent']) : 0.0;

        if ($current_time < 0) {
            $current_time = 0.0;
        }
        if ($duration < 0) {
            $duration = 0.0;
        }
        if ($percent < 0) {
            $percent = 0.0;
        }
        if ($percent > 100) {
            $percent = 100.0;
        }

        $source = '';
        if (isset($data['source']) && is_string($data['source'])) {
            $source = basename(str_replace('\\', '/', $data['source']));
            $source = preg_replace('/[^\w.\-]+/', '', $source);
            if (strlen($source) > 120) {
                $source = substr($source, 0, 120);
            }
        }

        $activity_type = $this->db->query_db_first(
            "SELECT id FROM activityType WHERE name = :name",
            array(':name' => 'video')
        );
        if (!$activity_type || empty($activity_type['id'])) {
            $this->router->skip_next_activity_log();
            return false;
        }

        $params = array(
            'type' => 'video',
            'event' => $event,
            'section_id' => $section_id,
            'currentTime' => round($current_time, 2),
            'duration' => round($duration, 2),
            'percent' => round($percent, 1),
        );
        if ($source !== '') {
            $params['source'] = $source;
        }

        $params_json = json_encode($params);
        if ($params_json === false) {
            $this->router->skip_next_activity_log();
            return false;
        }
        if (strlen($params_json) > 1000) {
            unset($params['source']);
            $params_json = json_encode($params);
            if ($params_json === false || strlen($params_json) > 1000) {
                $this->router->skip_next_activity_log();
                return false;
            }
        }

        $this->router->set_activity_override(array(
            'keyword' => $page_keyword,
            'params' => $params_json,
            'id_type' => intval($activity_type['id']),
            'skip_last_url' => true,
        ));

        return array('logged' => true);
    }
}
?>
