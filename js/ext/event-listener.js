/**
 * SelfHelp Event Listener - Lightweight polling for refresh events.
 *
 * When a page has enable_event_listener enabled, this script polls the
 * AjaxRefreshEvents endpoint for completed background tasks.
 * When events are found, the specified sections are refreshed via silent AJAX.
 *
 * Activated by a hidden element: <div data-event-listener="1" data-event-listener-interval="5">
 */
(function ($) {
    'use strict';

    var pollingTimers = {};

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initEventListeners, 0);
    } else {
        $(document).ready(function () {
            initEventListeners();
        });
    }

    function initEventListeners() {
        $('[data-event-listener="1"]').each(function () {
            var $el = $(this);
            var interval = parseInt($el.data('event-listener-interval')) || 5;
            var elId = $el.attr('id') || 'evt-' + Math.random().toString(36).substr(2, 9);

            if (pollingTimers[elId]) {
                return;
            }

            pollingTimers[elId] = setInterval(function () {
                checkRefreshEvents();
            }, interval * 1000);
        });
    }

    function checkRefreshEvents() {
        $.ajax({
            url: BASE_PATH + '/request/AjaxRefreshEvents/check',
            method: 'POST',
            dataType: 'json',
            timeout: 3000,
            cache: false,
            success: function (response) {
                if (response && response.success && response.data) {
                    var data = response.data;
                    if (data.refresh_sections && data.refresh_sections.length > 0) {
                        refreshSections(data.refresh_sections);
                    }
                }
            },
            error: function () {
                // Silent fail - polling continues
            }
        });
    }

    function refreshSections(sectionIds) {
        $.get(location.href, function (html) {
            var $newPage = $(html);
            sectionIds.forEach(function (sectionId) {
                var selector = '.style-section-' + sectionId;
                var $newSection = $newPage.find(selector);
                var $currentSection = $(selector);
                if ($newSection.length > 0 && $currentSection.length > 0) {
                    $currentSection.empty().append($newSection.children());
                }
            });
        });
    }

})(jQuery);
