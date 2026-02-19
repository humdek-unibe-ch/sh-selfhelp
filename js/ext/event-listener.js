/**
 * SelfHelp Event Listener - Lightweight polling for refresh events.
 *
 * When a page has enable_event_listener enabled, this script polls the
 * AjaxRefreshEvents endpoint for completed background tasks.
 * When events are found, the specified sections are refreshed via silent AJAX.
 *
 * Loading UX: Any component can opt in to loading indicators by adding
 * data-event-refresh-loading="1" to its wrapper element. When a form is
 * submitted on an event-listener page, sections containing such elements
 * receive a loading overlay (spinner + "Processing..." text). When the
 * section is refreshed via polling, the overlay is removed and a brief
 * highlight animation draws attention to the updated content.
 *
 * Activated by a hidden element: <div data-event-listener="1" data-event-listener-interval="5">
 */
(function ($) {
    'use strict';

    var STORAGE_KEY = 'selfhelp_event_loading_sections';
    var STALE_THRESHOLD_MS = 120000; // 2 minutes
    var pollingTimers = {};

    injectStyles();

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(init, 0);
    } else {
        $(document).ready(function () {
            init();
        });
    }

    function init() {
        restoreLoadingState();
        bindFormSubmitInterception();
        initEventListeners();
    }

    // ── Polling ────────────────────────────────────────────────────────

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
                    removeLoadingOverlay($currentSection);
                    $currentSection.empty().append($newSection.children());
                    highlightSection($currentSection);
                }
            });
            clearStoredSections(sectionIds);
        });
    }

    // ── Form Submit Interception ───────────────────────────────────────

    function bindFormSubmitInterception() {
        if ($('[data-event-listener="1"]').length === 0) {
            return;
        }

        $(document).on('submit', 'form', function () {
            var loadingSections = collectLoadingSectionIds();
            if (loadingSections.length === 0) {
                return;
            }

            var isAjax = $(this).find('input[name="ajax"]').val() == 1;

            if (isAjax) {
                applyOverlaysForSections(loadingSections);
            } else {
                storePendingSections(loadingSections);
            }
        });
    }

    function applyOverlaysForSections(sectionIds) {
        sectionIds.forEach(function (sectionId) {
            var $section = $('.style-section-' + sectionId);
            if ($section.length > 0) {
                applyLoadingOverlay($section);
            }
        });
    }

    function collectLoadingSectionIds() {
        var ids = [];
        $('[data-event-refresh-loading="1"]').each(function () {
            var sectionId = extractSectionId($(this));
            if (sectionId && ids.indexOf(sectionId) === -1) {
                ids.push(sectionId);
            }
        });
        return ids;
    }

    function extractSectionId($el) {
        var classes = ($el.attr('class') || '').split(/\s+/);
        for (var i = 0; i < classes.length; i++) {
            var match = classes[i].match(/^style-section-(\d+)$/);
            if (match) {
                return match[1];
            }
        }

        var $parent = $el.closest('[class*="style-section-"]');
        if ($parent.length > 0) {
            var parentClasses = ($parent.attr('class') || '').split(/\s+/);
            for (var j = 0; j < parentClasses.length; j++) {
                var parentMatch = parentClasses[j].match(/^style-section-(\d+)$/);
                if (parentMatch) {
                    return parentMatch[1];
                }
            }
        }
        return null;
    }

    // ── Session Storage ────────────────────────────────────────────────

    function storePendingSections(sectionIds) {
        try {
            var entry = {
                ids: sectionIds,
                timestamp: Date.now()
            };
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(entry));
        } catch (e) {
            // sessionStorage unavailable
        }
    }

    function restoreLoadingState() {
        try {
            var raw = sessionStorage.getItem(STORAGE_KEY);
            if (!raw) {
                return;
            }

            var entry = JSON.parse(raw);
            if (Date.now() - entry.timestamp > STALE_THRESHOLD_MS) {
                sessionStorage.removeItem(STORAGE_KEY);
                return;
            }

            entry.ids.forEach(function (sectionId) {
                var $section = $('.style-section-' + sectionId);
                if ($section.length > 0) {
                    applyLoadingOverlay($section);
                }
            });
        } catch (e) {
            sessionStorage.removeItem(STORAGE_KEY);
        }
    }

    function clearStoredSections(refreshedIds) {
        try {
            var raw = sessionStorage.getItem(STORAGE_KEY);
            if (!raw) {
                return;
            }

            var entry = JSON.parse(raw);
            var remaining = entry.ids.filter(function (id) {
                return refreshedIds.indexOf(parseInt(id)) === -1 &&
                       refreshedIds.indexOf(String(id)) === -1;
            });

            if (remaining.length === 0) {
                sessionStorage.removeItem(STORAGE_KEY);
            } else {
                entry.ids = remaining;
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify(entry));
            }
        } catch (e) {
            // ignore
        }
    }

    // ── Loading Overlay ────────────────────────────────────────────────

    function applyLoadingOverlay($section) {
        if ($section.find('.selfhelp-event-loading-overlay').length > 0) {
            return;
        }

        $section.css('position', 'relative');
        var $overlay = $(
            '<div class="selfhelp-event-loading-overlay">' +
                '<div class="selfhelp-event-loading-content">' +
                    '<div class="spinner-border text-primary" role="status">' +
                        '<span class="sr-only">Loading...</span>' +
                    '</div>' +
                    '<div class="selfhelp-event-loading-label">Processing...</div>' +
                '</div>' +
            '</div>'
        );
        $section.append($overlay);

        setTimeout(function () {
            removeLoadingOverlay($section);
        }, STALE_THRESHOLD_MS);
    }

    function removeLoadingOverlay($section) {
        $section.find('.selfhelp-event-loading-overlay').fadeOut(200, function () {
            $(this).remove();
        });
    }

    // ── Highlight Animation ────────────────────────────────────────────

    function highlightSection($section) {
        $section.addClass('selfhelp-event-refreshed');
        setTimeout(function () {
            $section.removeClass('selfhelp-event-refreshed');
        }, 1500);
    }

    // ── Injected CSS ───────────────────────────────────────────────────

    function injectStyles() {
        var css =
            '.selfhelp-event-loading-overlay {' +
                'position: absolute;' +
                'top: 0; left: 0; right: 0; bottom: 0;' +
                'background: rgba(255,255,255,0.85);' +
                'display: flex;' +
                'align-items: flex-start;' +
                'justify-content: center;' +
                'z-index: 100;' +
                'border-radius: inherit;' +
            '}' +
            '.selfhelp-event-loading-content {' +
                'text-align: center;' +
                'position: sticky;' +
                'top: 40%;' +
                'padding: 2rem 0;' +
            '}' +
            '.selfhelp-event-loading-label {' +
                'margin-top: 0.5rem;' +
                'font-size: 0.9rem;' +
                'color: #495057;' +
            '}' +
            '@keyframes selfhelpEventHighlight {' +
                '0%   { background-color: rgba(0,123,255,0.12); }' +
                '100% { background-color: transparent; }' +
            '}' +
            '.selfhelp-event-refreshed {' +
                'animation: selfhelpEventHighlight 1.5s ease-out;' +
            '}';

        var $style = $('<style>').attr('type', 'text/css').text(css);
        $('head').append($style);
    }

})(jQuery);
