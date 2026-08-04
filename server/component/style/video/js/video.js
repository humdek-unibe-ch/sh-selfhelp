/**
 * Video watch-progress tracking.
 * One independent tracker per <video data-track-interval> element.
 * play/ended fire immediately; heartbeats use the per-video interval.
 * After ended, heartbeats stop until the user plays again (new session).
 */
(function ($) {
    'use strict';

    function initVideoTracking() {
        $('video[data-track-interval]').each(function () {
            var $video = $(this);
            if ($video.data('shVideoTrackInit')) {
                return;
            }
            $video.data('shVideoTrackInit', true);
            bindTracker(this);
        });
    }

    function bindTracker(video) {
        var intervalSec = parseInt(video.getAttribute('data-track-interval'), 10) || 0;
        if (intervalSec <= 0) {
            return;
        }

        var sectionId = parseInt(video.getAttribute('data-section-id'), 10) || 0;
        var pageKeyword = video.getAttribute('data-page-keyword') || '';
        var source = video.getAttribute('data-source') || '';
        var heartbeatMs = intervalSec * 1000;
        var timerId = null;
        var sessionActive = false;

        function clearHeartbeat() {
            if (timerId !== null) {
                clearInterval(timerId);
                timerId = null;
            }
        }

        function buildPayload(eventName) {
            var currentTime = isFinite(video.currentTime) ? video.currentTime : 0;
            var duration = isFinite(video.duration) && video.duration > 0 ? video.duration : 0;
            var percent = 0;
            if (duration > 0) {
                percent = Math.min(100, Math.max(0, (currentTime / duration) * 100));
            } else if (eventName === 'ended') {
                percent = 100;
            }
            return {
                event: eventName,
                section_id: sectionId,
                page_keyword: pageKeyword,
                currentTime: currentTime,
                duration: duration,
                percent: percent,
                source: source
            };
        }

        function sendEvent(eventName) {
            if (!sectionId || !pageKeyword || typeof BASE_PATH === 'undefined') {
                return;
            }
            $.ajax({
                url: BASE_PATH + '/request/AjaxVideoTrack/track',
                method: 'POST',
                dataType: 'json',
                data: buildPayload(eventName),
                timeout: 5000,
                cache: false
            });
        }

        function startHeartbeat() {
            clearHeartbeat();
            timerId = setInterval(function () {
                if (video.paused || video.ended || !sessionActive) {
                    return;
                }
                sendEvent('heartbeat');
            }, heartbeatMs);
        }

        video.addEventListener('play', function () {
            // New session after ended (or first play). Pause/resume does not re-send play.
            var isNewSession = !sessionActive;
            sessionActive = true;
            if (isNewSession) {
                sendEvent('play');
            }
            startHeartbeat();
        });

        video.addEventListener('pause', function () {
            // Keep sessionActive until ended; pause only stops heartbeats temporarily.
            clearHeartbeat();
        });

        video.addEventListener('ended', function () {
            sendEvent('ended');
            clearHeartbeat();
            sessionActive = false;
        });

        function flushIfActive() {
            if (sessionActive && !video.ended) {
                sendEvent('heartbeat');
            }
        }

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') {
                flushIfActive();
            }
        });

        window.addEventListener('pagehide', flushIfActive);
    }

    $(document).ready(initVideoTracking);
})(jQuery);
