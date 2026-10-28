/**
 * Outline of the CMS UI (new UI).
 *
 * The CMS does not render the page. It shows the sections of the page as an
 * outline (a tree). The user selects a section and its properties are loaded
 * on the right. Sections can be added, imported, moved (buttons or drag and
 * drop) and removed in the outline. This file also provides the quick search
 * for the content and property fields of the properties.
 *
 * The functions `initOutline` and `initFieldSearch` are called by `init_ui_cms`
 * (cms_ui.js) every time the UI is (re-)initialized. They are idempotent.
 * The html of the outline is created by CmsView (tpl_page_outline.php).
 */

var outlineCollapsed = {}; // id of a section => true (collapsed) or false (expanded), the choice of the user
var outlineFilterText = '';
var outlineAddTarget = null; // where the section is added: {relation, parentId, insertUrl, updateUrl, position, description}
var outlineIsBusy = false;
var outlineBusyCount = 0; // number of running requests
var outlineBusyTimer = null;
var outlineLastUrl = null;
var outlineEventsBound = false;
var OUTLINE_LARGE_PAGE = 120; // an outline with more sections is collapsed below the second level by default

//******************************************* Init ***********************************************

/**
 * Init the outline. Safe to call multiple times: all events are delegated and
 * the elements which are created by the server are initialized only once.
 */
function initOutline() {
    outlineBindEvents();
    outlineLastUrl = location.href.split('#')[0];
    var $outline = $('#ui-outline');
    if ($outline.length === 0) {
        return;
    }
    outlineApplyCollapsedState();
    outlineInitSortable();
    var $filter = $('#ui-outline-filter');
    if ($filter.length) {
        $filter.val(outlineFilterText);
    }
    outlineApplyFilter(outlineFilterText);
    outlineReveal($outline.find('.ui-outline-node.is-selected'), false);
}

/**
 * Bind all events of the outline and its modals. The events are delegated to
 * the document, so they work after the html was replaced and are bound once.
 */
function outlineBindEvents() {
    if (outlineEventsBound) {
        return;
    }
    outlineEventsBound = true;
    $(document)
        .on('click.outline', '#ui-outline .ui-outline-link', function (e) {
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.which === 2) {
                return; // let the browser open a new tab
            }
            e.preventDefault();
            outlineSelect($(this).attr('href'));
        })
        .on('click.outline', '#ui-breadcrumb a', function (e) {
            // breadcrumb items of the same page are loaded without leaving the page
            var pageId = $('#ui-outline').attr('data-page-id');
            var href = $(this).attr('href');
            if (!pageId || !href || e.ctrlKey || e.metaKey || e.shiftKey || e.which === 2) {
                return;
            }
            if (new RegExp('/cms(_update)?/' + pageId + '(/|$)').test(href)) {
                e.preventDefault();
                outlineSelect(href);
            }
        })
        .on('click.outline', '#ui-outline [data-action]', function (e) {
            e.preventDefault();
            if (!outlineIsBusy) {
                outlineHandleAction($(this).data('action'), $(this).closest('.ui-outline-node'), $(this));
            }
        })
        .on('click.outline', '#ui-outline-add', function () {
            outlineOpenAdd(outlineRootTarget(), false);
        })
        .on('click.outline', '#ui-outline-import', function () {
            outlineOpenAdd(outlineRootTarget(), true);
        })
        .on('click.outline', '#ui-outline-expand-all', function () {
            outlineSetAllCollapsed(false);
        })
        .on('click.outline', '#ui-outline-collapse-all', function () {
            outlineSetAllCollapsed(true);
        })
        .on('input.outline', '#ui-outline-filter', function () {
            outlineApplyFilter($(this).val());
        })
        .on('keydown.outline', '#ui-outline-filter', function (e) {
            if (e.key === 'Escape') {
                $(this).val('');
                outlineApplyFilter('');
            }
        })
        // the modal which adds sections
        .on('click.outline', '#ui-new-section-btn', function () {
            var styleId = parseInt($('#ui-new-section-select').val(), 10);
            if (!styleId) {
                $.alert({ title: 'CMS UI', content: 'Please select a style!' });
                return;
            }
            var styleName = $('#ui-new-section-select option:selected').text();
            outlineCreateSection(styleId, styleName);
        })
        .on('click.outline', '#ui-unassigned-section-btn', function () {
            outlineInsertExistingSection(parseInt($('#ui-unassigned-section-select').val(), 10));
        })
        .on('click.outline', '#ui-reference-section-btn', function () {
            outlineInsertExistingSection(parseInt($('#ui-reference-section-select').val(), 10));
        })
        .on('change.outline', '#ui-new-section-select', function () {
            // the html was rendered on the server from the (markdown) description of the style, raw html is escaped there
            var html = $('#ui-new-section-select option:selected').attr('data-description-html') || '';
            $('#ui-new-section-description').html(html).toggleClass('d-none', html === '');
        })
        .on('shown.bs.modal.outline', '#ui-add-section-modal', function () {
            $('#nav-tab .nav-link.active').trigger('focus');
        })
        .on('submit.outline', '#cmsImportJson[data-ajax]', function (e) {
            e.preventDefault();
            outlineImportSection($(this));
        })
        // the modal which imports a page
        .on('click.outline', '.ui-import-page-trigger', function (e) {
            e.preventDefault();
            outlineOpenImportPage();
        })
        .on('cms-import:loaded.outline', '#ui-import-page-form', function (e, json) {
            var $keyword = $('#ui-import-page-keyword');
            if (!$keyword.val() && json && json.page && json.page.keyword) {
                $keyword.val(json.page.keyword);
            }
        })
        .on('submit.outline', '#ui-import-page-form', function (e) {
            e.preventDefault();
            outlineImportPage($(this));
        });
    $(window).on('popstate.outline', function () {
        if ($('#ui-outline').length === 0 || location.href.split('#')[0] === outlineLastUrl) {
            return;
        }
        var lastUrl = outlineLastUrl;
        outlineConfirmDiscard(function () {
            outlineLoad(location.href, { push: false, full: true });
        }, function () {
            history.pushState(null, '', lastUrl); // stay on the page with the unsaved changes
        });
    });
}

//******************************************* Helpers ***********************************************

/**
 * Escape a text which is used in html.
 * @param {any} text
 * @returns {String}
 */
function outlineEsc(text) {
    return $('<div>').text(text === undefined || text === null ? '' : String(text)).html();
}

/**
 * Find the elements in the parsed html of a page. The root elements are included.
 * @param {jQuery} $data the parsed html
 * @param {String} selector
 * @returns {jQuery}
 */
function outlineFind($data, selector) {
    return $data.find(selector).addBack(selector);
}

/**
 * Start or end the busy state. A short request does not flicker because the
 * state is shown only after a short delay. All input is blocked while busy.
 * Every call with true must be followed by a call with false.
 * @param {Boolean} busy
 */
function outlineSetBusy(busy) {
    outlineBusyCount = Math.max(0, outlineBusyCount + (busy ? 1 : -1));
    outlineIsBusy = outlineBusyCount > 0;
    clearTimeout(outlineBusyTimer);
    if (outlineIsBusy) {
        outlineBusyTimer = setTimeout(function () {
            $('body').addClass('cms-busy');
        }, 150);
    } else {
        $('body').removeClass('cms-busy');
    }
}

/**
 * Send a request and return a promise. The answer is the html of the page the
 * server returns after all redirects.
 * @param {String} method get or post
 * @param {String} url
 * @param {Object} params the post parameters (name => value)
 * @returns {Promise} resolved with {html, url, redirected}
 */
function outlineRequest(method, url, params) {
    var deferred = $.Deferred();
    var init = {
        method: method,
        credentials: 'same-origin',
        cache: 'no-store',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    };
    if (params) {
        var body = new URLSearchParams();
        Object.keys(params).forEach(function (key) {
            if (params[key] !== undefined && params[key] !== null) {
                body.append(key, params[key]);
            }
        });
        init.body = body;
    }
    fetch(url, init).then(function (response) {
        return response.text().then(function (html) {
            if (response.ok) {
                deferred.resolve({ html: html, url: response.url, redirected: response.redirected });
            } else {
                deferred.reject({ status: response.status, html: html });
            }
        });
    }, function (error) {
        deferred.reject({ status: 0, error: error });
    });
    return deferred.promise();
}

/**
 * Show an error message.
 * @param {String} message html
 */
function outlineError(message) {
    $.alert({
        title: 'Error!',
        type: 'red',
        content: message
    });
}

/**
 * Show the error alerts of a page the server returned (e.g. "Failed to add a new section").
 * @param {jQuery} $data the parsed html of the page
 * @returns {Boolean} true if there was an error
 */
function outlineShowServerErrors($data) {
    var messages = [];
    outlineFind($data, '#cms-alerts .alert-danger').each(function () {
        messages.push(outlineEsc($(this).text().replace(/\s+/g, ' ').replace(/^\s*×\s*/, '').trim()));
    });
    if (messages.length > 0) {
        outlineError(messages.join('<br>'));
    }
    return messages.length > 0;
}

/**
 * Execute a function if there are no unsaved changes in the properties or if
 * the user agrees to discard them.
 * @param {Function} proceed
 * @param {Function} cancel executed if the user wants to keep the changes
 */
function outlineConfirmDiscard(proceed, cancel) {
    if (typeof unsavedChanges === 'undefined' || unsavedChanges.length === 0) {
        proceed();
        return;
    }
    $.confirm({
        title: 'Unsaved changes',
        content: 'There are unsaved changes in the properties. Do you want to discard them?',
        type: 'orange',
        buttons: {
            discard: {
                text: 'Discard changes',
                btnClass: 'btn-orange',
                action: function () {
                    unsavedChanges = [];
                    proceed();
                }
            },
            cancel: {
                text: 'Keep editing',
                action: function () {
                    if (cancel) {
                        cancel();
                    }
                }
            }
        }
    });
}

//******************************************* Properties state ***********************************************

/**
 * Check if a card of the properties is expanded.
 * @param {jQuery} $card
 * @returns {Boolean} null if the card does not exist
 */
function outlineCardExpanded($card) {
    if ($card.length === 0) {
        return null;
    }
    if ($card.data('searchPrev') !== undefined) {
        return $card.data('searchPrev'); // the card is only expanded by the field search
    }
    return $card.children('.card-body').hasClass('show');
}

/**
 * Expand or collapse a card of the properties without animation.
 * @param {jQuery} $card
 * @param {Boolean} expanded
 */
function outlineSetCardExpanded($card, expanded) {
    var $header = $card.children('.card-header');
    var $body = $card.children('.card-body');
    var $icon = $header.find('i.card-icon-collapse');
    $body.toggleClass('show', expanded).css('display', '');
    $header.toggleClass('collapsed', !expanded);
    $icon.toggleClass('fa-angle-double-up', expanded).toggleClass('fa-angle-double-down', !expanded);
    if (expanded) {
        $card.find('.CodeMirror').each(function () {
            if (this.CodeMirror) {
                this.CodeMirror.refresh(); // editors in hidden cards have no size
            }
        });
    }
}

/**
 * The menus of the sidebar. They are replaced when the CMS is updated with
 * the answer of the server, the choice of the user (expanded or collapsed) stays.
 */
var OUTLINE_SIDEBAR_CARDS = [
    '#section-ui-page-list',
    '#section-ui-global-page-list',
    '#section-ui-navigation-hierarchy-list'
];

/**
 * All cards which are replaced when the properties of another section are loaded.
 */
var OUTLINE_STATE_CARDS = ['#section-ui-card-content', '#section-ui-card-properties'].concat(OUTLINE_SIDEBAR_CARDS);

/**
 * Remember which cards are expanded, such that the choice of the user stays
 * when the CMS is updated.
 * @param {Array} selectors the cards, by default all cards of OUTLINE_STATE_CARDS
 * @returns {Object} the expanded state per card (null if the card does not exist)
 */
function outlineCaptureCardState(selectors) {
    var state = {};
    (selectors || OUTLINE_STATE_CARDS).forEach(function (selector) {
        state[selector] = outlineCardExpanded($(selector));
    });
    return state;
}

/**
 * Restore the state of the cards.
 * @param {Object} state see outlineCaptureCardState
 */
function outlineApplyCardState(state) {
    Object.keys(state).forEach(function (selector) {
        if (state[selector] !== null) {
            outlineSetCardExpanded($(selector), state[selector]);
        }
    });
}

//******************************************* Loading ***********************************************

/**
 * Select a section: load its properties and mark it in the outline.
 * @param {String} url the url of the section
 */
function outlineSelect(url) {
    if (outlineIsBusy) {
        return;
    }
    outlineConfirmDiscard(function () {
        outlineLoad(url, { push: true });
    });
}

/**
 * Load a page of the CMS and show it.
 * @param {String} url
 * @param {Object} opts
 *  - push:  add the url to the browser history
 *  - full:  replace the outline too (otherwise only if it shows something else)
 */
function outlineLoad(url, opts) {
    outlineSetBusy(true);
    outlineRequest('GET', url).done(function (res) {
        outlineApplyPage(res.html, res.url, opts || {});
    }).fail(function () {
        outlineError('The page could not be loaded.');
    }).always(function () {
        outlineSetBusy(false);
    });
}

/**
 * Show a page the server returned: the properties and, if needed, the outline.
 * @param {String} html the html of the page
 * @param {String} url the final url of the page
 * @param {Object} opts see outlineLoad
 */
function outlineApplyPage(html, url, opts) {
    var $data = $(html);
    if (outlineFind($data, '#properties').length === 0 || outlineFind($data, '#ui-middle').length === 0) {
        window.location.href = url; // it is not a page of the CMS (e.g. the login page)
        return;
    }
    var cardState = outlineCaptureCardState();
    var contextChanged = outlineFind($data, '#ui-outline').attr('data-context') !== $('#ui-outline').attr('data-context');
    var full = opts.full || contextChanged;
    var selected = parseInt(outlineFind($data, '#ui-outline').attr('data-selected'), 10) || 0;
    var elements = ['#properties', '#ui-breadcrumb'];
    if (full) {
        elements = ['#ui-middle', '#properties'];
    }
    if (contextChanged) {
        // another page or navigation item: the menus of the sidebar show something else too
        elements.push('#section-ui-navigation-hierarchy-list', '#section-ui-page-list', '#section-ui-global-page-list');
    }
    if (opts.push && url.split('#')[0] !== location.href.split('#')[0]) {
        history.pushState({ cmsOutline: true }, '', url);
    }
    update_new_data($data, elements, function () {
        if (!full) {
            outlineMarkSelected(selected, true);
        }
    }, function () {
        outlineApplyCardState(cardState);
        outlineMarkSelected(selected, true);
        if (opts.flash) {
            outlineFlash(selected);
        }
    });
}

/**
 * Replace the outline with the outline of a page the server returned. The
 * properties and the breadcrumb are not changed, the selected section stays
 * selected.
 * @param {String} html the html of the page
 * @param {Number} flashId the section which is highlighted
 */
function outlineApplyOutline(html, flashId) {
    var $data = $(html);
    var $newOutline = outlineFind($data, '#ui-outline');
    if ($newOutline.length === 0 || $('#ui-outline').length === 0) {
        window.location.reload();
        return;
    }
    var selected = parseInt($('#ui-outline').attr('data-selected'), 10) || 0;
    $('.popover').remove();
    $('#ui-outline').replaceWith($newOutline);
    var $toolbar = outlineFind($data, '#ui-outline-toolbar');
    if ($toolbar.length) {
        $('#ui-outline-toolbar').replaceWith($toolbar);
    }
    initOutline();
    outlineMarkSelected(selected, false);
    if (flashId) {
        outlineFlash(flashId);
    }
}

/**
 * Load the outline from the server and show it. The properties are not changed.
 * @param {Function} callback executed after the outline is shown
 */
function outlineRefresh(callback) {
    outlineSetBusy(true);
    outlineRequest('GET', location.href).done(function (res) {
        outlineApplyOutline(res.html);
        if (callback) {
            callback();
        }
    }).fail(function () {
        outlineError('The outline could not be loaded.');
    }).always(function () {
        outlineSetBusy(false);
    });
}

/**
 * Mark a section as selected in the outline.
 * @param {Number} id the section id or 0 for the page
 * @param {Boolean} scroll if true the section is scrolled into view
 */
function outlineMarkSelected(id, scroll) {
    var $outline = $('#ui-outline');
    $outline.find('.is-selected').removeClass('is-selected');
    $outline.attr('data-selected', id || 0);
    if (!id) {
        $outline.children('.ui-outline-page-row').addClass('is-selected');
        return;
    }
    var $nodes = $outline.find('.ui-outline-node[data-id="' + id + '"]');
    $nodes.addClass('is-selected').children('.ui-outline-row').addClass('is-selected');
    outlineReveal($nodes, scroll);
}

/**
 * Highlight a section for a moment.
 * @param {Number} id
 */
function outlineFlash(id) {
    if (!id) {
        return;
    }
    var $row = $('#ui-outline .ui-outline-node[data-id="' + id + '"] > .ui-outline-row').addClass('is-flash');
    setTimeout(function () {
        $row.removeClass('is-flash');
    }, 1800);
}

//******************************************* Collapse and filter ***********************************************

/**
 * Collapse or expand the nodes which have children as the user chose. Nodes
 * which were not touched by the user are expanded, except in a big outline
 * where everything below the second level is collapsed.
 */
function outlineApplyCollapsedState() {
    var large = parseInt($('#ui-outline').attr('data-count'), 10) > OUTLINE_LARGE_PAGE;
    $('#ui-outline .ui-outline-node.has-children').each(function () {
        var $node = $(this);
        var id = $node.attr('data-id');
        var depth = parseInt($node.parent('.ui-outline-list').attr('data-depth'), 10) || 0;
        var collapsed = outlineCollapsed.hasOwnProperty(id) ? outlineCollapsed[id] : (large && depth >= 1);
        $node.toggleClass('is-collapsed', collapsed);
    });
}

/**
 * Expand all parents of a node.
 * @param {jQuery} $nodes the nodes
 * @param {Boolean} scroll if true the first node is scrolled into view
 */
function outlineReveal($nodes, scroll) {
    if ($nodes.length === 0) {
        return;
    }
    $nodes.parents('.ui-outline-node.is-collapsed').each(function () {
        $(this).removeClass('is-collapsed');
        outlineCollapsed[$(this).attr('data-id')] = false;
    });
    if (scroll && $nodes[0].scrollIntoView) {
        $nodes[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
}

/**
 * Collapse or expand a single node.
 * @param {jQuery} $node
 */
function outlineToggleNode($node) {
    var collapsed = !$node.hasClass('is-collapsed');
    $node.toggleClass('is-collapsed', collapsed);
    outlineCollapsed[$node.attr('data-id')] = collapsed;
}

/**
 * Collapse or expand all nodes.
 * @param {Boolean} collapsed
 */
function outlineSetAllCollapsed(collapsed) {
    $('#ui-outline .ui-outline-node.has-children').each(function () {
        $(this).toggleClass('is-collapsed', collapsed);
        outlineCollapsed[$(this).attr('data-id')] = collapsed;
    });
    if (!collapsed) {
        return;
    }
    outlineReveal($('#ui-outline .ui-outline-node.is-selected'), false); // the selected section stays visible
}

/**
 * Show only the sections which match the filter and the parents of these
 * sections. All words of the filter must be found in the name, style, text
 * or id of a section.
 * @param {String} text the filter
 */
function outlineApplyFilter(text) {
    outlineFilterText = text;
    var tokens = text.toLowerCase().split(/\s+/).filter(Boolean);
    var $outline = $('#ui-outline');
    var $nodes = $outline.find('.ui-outline-node');
    $outline.toggleClass('is-filtering', tokens.length > 0);
    $nodes.removeClass('is-match is-filtered-out has-match-descendant');
    var matches = 0;
    if (tokens.length > 0) {
        $nodes.addClass('is-filtered-out');
        $nodes.each(function () {
            var search = $(this).attr('data-search') || '';
            var found = tokens.every(function (token) {
                return search.indexOf(token) !== -1;
            });
            if (found) {
                matches++;
                $(this).addClass('is-match').removeClass('is-filtered-out');
                $(this).parents('.ui-outline-node').addClass('has-match-descendant').removeClass('is-filtered-out');
            }
        });
    }
    $('#ui-outline-no-match').toggleClass('d-none', tokens.length === 0 || matches > 0);
    var total = parseInt($outline.attr('data-count'), 10) || 0;
    $('#ui-outline-count').text(tokens.length > 0 ? matches + ' of ' + total : (total === 1 ? '1 section' : total + ' sections'));
}

//******************************************* Actions ***********************************************

/**
 * Execute an action of an outline button.
 * @param {String} action
 * @param {jQuery} $node the node of the button
 * @param {jQuery} $button the button
 */
function outlineHandleAction(action, $node, $button) {
    switch (action) {
        case 'toggle':
            outlineToggleNode($node);
            break;
        case 'add-above':
        case 'add-below':
        case 'add-inside':
            outlineOpenAdd(outlineNodeTarget($node, action), false);
            break;
        case 'add-inside-list':
            outlineOpenAdd(outlineListTarget($button.closest('.ui-outline-list')), false);
            break;
        case 'add-root':
            outlineOpenAdd(outlineRootTarget(), false);
            break;
        case 'import-root':
            outlineOpenAdd(outlineRootTarget(), true);
            break;
        case 'move-up':
            outlineMove($node, -1);
            break;
        case 'move-down':
            outlineMove($node, 1);
            break;
        case 'remove':
            outlineRemove($node);
            break;
    }
}

/**
 * Calculate the position (order value) of a section which is inserted
 * between two siblings. The server renumbers the positions after the insert.
 * @param {jQuery} $prev the previous sibling, can be empty
 * @param {jQuery} $next the next sibling, can be empty
 * @returns {Number} undefined if the list is empty (the server appends)
 */
function outlineInsertPosition($prev, $next) {
    var prev = $prev.length ? parseInt($prev.attr('data-position'), 10) : null;
    var next = $next.length ? parseInt($next.attr('data-position'), 10) : null;
    var position;
    if (prev === null && next === null) {
        return undefined;
    } else if (prev === null) {
        position = next - 5;
    } else if (next === null) {
        position = prev + 5;
    } else {
        position = prev + Math.max(1, Math.floor((next - prev) / 2));
    }
    return position === 0 ? -1 : position; // the server appends if the position is 0
}

/**
 * Get the information of a list of the outline which is needed to add and remove sections.
 * @param {jQuery} $list
 * @returns {Object}
 */
function outlineListInfo($list) {
    return {
        relation: $list.attr('data-relation'),
        parentId: parseInt($list.attr('data-parent-id'), 10),
        insertUrl: $list.attr('data-insert-url'),
        updateUrl: $list.attr('data-update-url')
    };
}

/**
 * The target where a section is added at the end of a list.
 * @param {jQuery} $list
 * @returns {Object}
 */
function outlineListTarget($list) {
    var target = outlineListInfo($list);
    var $parent = $list.parent('.ui-outline-node');
    target.description = $parent.length ? 'Inside <code>' + outlineEsc($parent.attr('data-name')) + '</code>' : 'On the page';
    return target;
}

/**
 * The default target: the end of the page (or of the navigation item).
 * @returns {Object}
 */
function outlineRootTarget() {
    var $outline = $('#ui-outline');
    var target = {
        relation: $outline.attr('data-add-relation'),
        parentId: parseInt($outline.attr('data-add-parent-id'), 10),
        insertUrl: $outline.attr('data-add-insert-url'),
        updateUrl: $outline.attr('data-add-update-url')
    };
    if (target.relation === RELATION_PAGE_CHILDREN) {
        target.description = 'At the end of the page <code>' + outlineEsc($outline.children('.ui-outline-page-row').find('.ui-outline-name').text()) + '</code>';
    } else {
        var $parent = $outline.find('.ui-outline-node[data-id="' + target.parentId + '"]').first();
        target.description = 'At the end of <code>' + outlineEsc($parent.attr('data-name')) + '</code>';
    }
    return target;
}

/**
 * The target where a section is added next to or inside a node.
 * @param {jQuery} $node
 * @param {String} action add-above, add-below or add-inside
 * @returns {Object}
 */
function outlineNodeTarget($node, action) {
    var name = '<code>' + outlineEsc($node.attr('data-name')) + '</code>';
    if (action === 'add-inside') {
        var inside = outlineListInfo($node.children('.ui-outline-list'));
        inside.description = 'Inside ' + name;
        return inside;
    }
    var target = outlineListInfo($node.parent('.ui-outline-list'));
    if (action === 'add-above') {
        target.position = outlineInsertPosition($node.prev('.ui-outline-node'), $node);
        target.description = 'Above ' + name;
    } else {
        target.position = outlineInsertPosition($node, $node.next('.ui-outline-node'));
        target.description = 'Below ' + name;
    }
    return target;
}

/**
 * Open the modal which adds a section.
 * @param {Object} target see outlineAddTarget
 * @param {Boolean} showImport open the tab which imports a section
 */
function outlineOpenAdd(target, showImport) {
    var $modal = $('#ui-add-section-modal');
    if ($modal.length === 0 || !target || !target.insertUrl) {
        return;
    }
    outlineAddTarget = target;
    $('#ui-add-section-target').html(target.description);
    $('#ui-new-section-select, #ui-unassigned-section-select, #ui-reference-section-select').each(function () {
        $(this).prop('selectedIndex', 0).selectpicker('refresh');
    });
    $('#ui-new-section-description').empty().addClass('d-none');
    var $import = $('#cmsImportJson');
    if ($import.length) {
        $import[0].reset();
        $import.find('.custom-file-label').text('Choose a JSON file').addClass('text-muted');
        $import.find('input[name="json"]').val('');
        $import.data('versionMessage', null).data('versionConfirmed', false);
    }
    $('#ui-import-section-error').addClass('d-none').empty();
    $(showImport && $('#nav-import-section-tab').length ? '#nav-import-section-tab' : '#nav-new-section-tab').tab('show');
    $modal.modal('show');
}

/**
 * Create a new section of a style at the target.
 * @param {Number} styleId
 * @param {String} styleName
 */
function outlineCreateSection(styleId, styleName) {
    var target = outlineAddTarget;
    var timestamp = Math.round((new Date()).getTime() / 1000);
    $('#ui-add-section-modal').modal('hide');
    outlineAddSection(target, {
        mode: 'insert',
        relation: target.relation,
        'add-section-link': '',
        'section-name': timestamp + '-' + styleName,
        'section-style': styleId,
        position: target.position
    });
}

/**
 * Add an existing section at the target.
 * @param {Number} sectionId
 */
function outlineInsertExistingSection(sectionId) {
    var target = outlineAddTarget;
    if (!sectionId) {
        $.alert({ title: 'CMS UI', content: 'Please select a section!' });
        return;
    }
    // a section cannot be a child of itself or of one of its children
    var $target = $('#ui-outline .ui-outline-list').filter(function () {
        return $(this).attr('data-parent-id') == target.parentId && $(this).attr('data-relation') === target.relation;
    }).first();
    var $own = $('#ui-outline .ui-outline-node[data-id="' + sectionId + '"]');
    if (target.relation === RELATION_SECTION_CHILDREN && (sectionId === target.parentId || $own.find($target).length > 0)) {
        $.alert({ title: 'CMS UI', content: 'It is not possible to insert a section inside itself!' });
        return;
    }
    $('#ui-add-section-modal').modal('hide');
    outlineAddSection(target, {
        mode: 'insert',
        relation: target.relation,
        'add-section-link': sectionId,
        position: target.position
    });
}

/**
 * Send the request which adds a section (new or existing). The server answers
 * with a redirect to the new section, the properties of the section are shown.
 * @param {Object} target
 * @param {Object} params the post parameters
 */
function outlineAddSection(target, params) {
    outlineConfirmDiscard(function () {
        outlineFilterText = '';
        outlineSetBusy(true);
        outlineRequest('POST', target.insertUrl, params).done(function (res) {
            var $data = $(res.html);
            if (!res.redirected) {
                outlineShowServerErrors($data);
                outlineApplyOutline(res.html);
                return;
            }
            outlineApplyPage(res.html, res.url, { push: true, full: true, flash: true });
        }).fail(function () {
            outlineError('The section was not added!');
        }).always(function () {
            outlineSetBusy(false);
        });
    });
}

/**
 * Import a section from a json file at the target.
 * @param {jQuery} $form the import form of the modal
 */
function outlineImportSection($form) {
    var target = outlineAddTarget;
    var $error = $('#ui-import-section-error').addClass('d-none').empty();
    if (!$form.find('input[name="json"]').val()) {
        $.alert({ title: 'CMS Import', content: 'Please select a valid JSON file.' });
        return;
    }
    confirmImportVersion($form, function () {
        outlineConfirmDiscard(function () {
            var params = {
                json: $form.find('input[name="json"]').val(),
                relation: target.relation,
                page_id: $('#ui-outline').attr('data-page-id'),
                parent_id: target.parentId,
                position: target.position
            };
            outlineFilterText = '';
            outlineSetBusy(true);
            outlineRequest('POST', $form.attr('action'), params).done(function (res) {
                var $data = $(res.html);
                var $result = outlineFind($data, '#cms-import-result');
                if ($result.length === 0) {
                    var messages = [];
                    outlineFind($data, '.alert-danger').each(function () {
                        messages.push(outlineEsc($(this).text().replace(/\s+/g, ' ').replace(/^\s*×\s*/, '').trim()));
                    });
                    $error.html(messages.length ? messages.join('<br>') : 'The section was not imported.').removeClass('d-none');
                    return;
                }
                $('#ui-add-section-modal').modal('hide');
                var newId = parseInt($result.attr('data-id'), 10);
                // show the new section: load the outline and select the section
                outlineRefresh(function () {
                    var href = $('#ui-outline .ui-outline-node[data-id="' + newId + '"] > .ui-outline-row .ui-outline-link').first().attr('href');
                    if (href) {
                        outlineLoad(href, { push: true, flash: true });
                    }
                });
            }).fail(function () {
                $error.html('The section was not imported.').removeClass('d-none');
            }).always(function () {
                outlineSetBusy(false);
            });
        });
    });
}

/**
 * Remove a section from its parent (the section is not deleted).
 * @param {jQuery} $node
 */
function outlineRemove($node) {
    var $list = $node.parent('.ui-outline-list');
    var info = outlineListInfo($list);
    var id = parseInt($node.attr('data-id'), 10);
    var where = info.relation === RELATION_PAGE_CHILDREN ? 'the page' : 'its parent';
    confirmation('Do you really want to remove <code>' + outlineEsc($node.attr('data-name')) + '</code> from ' + where + '?' +
        '<br><small class="text-muted">The section is not deleted. It can be added again with <em>Unassigned Section</em> or <em>Reference Section</em>.</small>', function () {
            // if the selected section is removed (or one of its children) its properties are not valid anymore:
            // the parent is selected (or the page if the section is on the top level)
            var affectsSelection = $node.hasClass('is-selected') || $node.find('.ui-outline-node.is-selected').length > 0;
            var parentUrl = $list.parent('.ui-outline-node').children('.ui-outline-row').find('.ui-outline-link').attr('href') || $('#ui-outline').attr('data-page-url');
            var send = function () {
                outlineSetBusy(true);
                outlineRequest('POST', info.updateUrl, {
                    'remove-section-link': id,
                    mode: 'delete',
                    relation: info.relation
                }).done(function (res) {
                    outlineShowServerErrors($(res.html));
                    if (affectsSelection) {
                        outlineLoad(parentUrl, { push: true, full: true });
                    } else {
                        outlineApplyOutline(res.html);
                    }
                }).fail(function () {
                    outlineError('The section was not removed!');
                }).always(function () {
                    outlineSetBusy(false);
                });
            };
            if (affectsSelection) {
                outlineConfirmDiscard(send);
            } else {
                send();
            }
        });
}

//******************************************* Move and drag and drop ***********************************************

/**
 * Save the order of the sections of a list as it is in the DOM.
 * @param {jQuery} $list
 * @param {Number} flashId the section which is highlighted afterwards
 */
function outlineSaveOrder($list, flashId) {
    var info = outlineListInfo($list);
    var order = [];
    $list.children('.ui-outline-node').each(function (idx) {
        order[parseInt($(this).attr('data-index'), 10)] = idx * 10; // the key is the old order (see the update of the children order)
    });
    outlineSetBusy(true);
    outlineRequest('POST', info.updateUrl, {
        mode: 'update',
        'fields[sections][1][1][id]': '',
        'fields[sections][1][1][type]': 'section-list',
        'fields[sections][1][1][relation]': info.relation,
        'fields[sections][1][1][content]': order.join()
    }).done(function (res) {
        outlineShowServerErrors($(res.html));
        outlineApplyOutline(res.html, flashId);
    }).fail(function () {
        outlineError('The section was not re-ordered!');
        outlineRefresh();
    }).always(function () {
        outlineSetBusy(false);
    });
}

/**
 * Move a section one position up or down in its list.
 * @param {jQuery} $node
 * @param {Number} direction -1 up, 1 down
 */
function outlineMove($node, direction) {
    var $sibling = direction < 0 ? $node.prev('.ui-outline-node') : $node.next('.ui-outline-node');
    if ($sibling.length === 0) {
        return;
    }
    if (direction < 0) {
        $node.insertBefore($sibling);
    } else {
        $node.insertAfter($sibling);
    }
    outlineSaveOrder($node.parent('.ui-outline-list'), parseInt($node.attr('data-id'), 10));
}

/**
 * Make the lists of the outline sortable (drag and drop). Sections can be
 * moved within a list and to another list.
 */
function outlineInitSortable() {
    var $outline = $('#ui-outline');
    if ($outline.attr('data-editable') !== '1' || typeof Sortable === 'undefined') {
        return;
    }
    $outline.find('.ui-outline-list').each(function () {
        var $list = $(this);
        // the list with the (pinned) root of a navigation item cannot be changed
        if ($list.data('outlineSortable') || $list.children('.ui-outline-node.is-pinned').length > 0) {
            return;
        }
        $list.data('outlineSortable', new Sortable(this, {
            group: 'ui-outline',
            handle: '.ui-outline-handle',
            draggable: '.ui-outline-node:not(.is-pinned)',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            ghostClass: 'ui-outline-ghost',
            onMove: function (evt) {
                return !evt.dragged.contains(evt.to); // never drop a section into itself
            },
            onEnd: outlineOnDrop
        }));
    });
}

/**
 * A section was dropped. Within the same list the order is saved, in another
 * list the section is moved there.
 * @param {Object} evt the event of Sortable
 */
function outlineOnDrop(evt) {
    var $item = $(evt.item);
    var id = parseInt($item.attr('data-id'), 10);
    if (evt.from === evt.to) {
        if (evt.oldDraggableIndex !== evt.newDraggableIndex) {
            outlineSaveOrder($(evt.to), id);
        }
        return;
    }
    var from = outlineListInfo($(evt.from));
    var to = outlineListInfo($(evt.to));
    var position = outlineInsertPosition($item.prevAll('.ui-outline-node').first(), $item.nextAll('.ui-outline-node').first());
    outlineSetBusy(true);
    // one request: the server adds the section to the new list and removes it from the old list or it changes nothing
    outlineRequest('POST', to.insertUrl, {
        mode: 'insert',
        relation: to.relation,
        'move-section-link': id,
        'from-relation': from.relation,
        'from-parent': from.parentId,
        position: position
    }).done(function (res) {
        // the answer shows the real state, also if the server refused to move the section
        var failed = outlineShowServerErrors($(res.html));
        outlineApplyOutline(res.html, failed ? undefined : id);
    }).fail(function () {
        outlineError('The section was not moved!');
        outlineRefresh(); // show the real state
    }).always(function () {
        outlineSetBusy(false);
    });
}

//******************************************* Import page ***********************************************

/**
 * Open the modal which imports a page.
 */
function outlineOpenImportPage() {
    var $modal = $('#ui-import-page-modal');
    if ($modal.length === 0) {
        return;
    }
    outlineConfirmDiscard(function () {
        var $form = $('#ui-import-page-form');
        $form[0].reset();
        $form.find('.custom-file-label').text('Choose a JSON file').addClass('text-muted');
        $form.find('input[name="json"]').val('');
        $form.data('versionMessage', null).data('versionConfirmed', false);
        $('#ui-import-page-error').addClass('d-none').empty();
        $modal.modal('show');
    });
}

/**
 * Import the page of the selected json file. If it succeeds the new page is opened.
 * @param {jQuery} $form the form of the modal
 */
function outlineImportPage($form) {
    var $error = $('#ui-import-page-error').addClass('d-none').empty();
    if (!$form.find('input[name="json"]').val()) {
        $error.text('Please select a valid JSON file.').removeClass('d-none');
        return;
    }
    confirmImportVersion($form, function () {
        $('#ui-import-page-submit').prop('disabled', true);
        outlineRequest('POST', $form.attr('action'), {
            json: $form.find('input[name="json"]').val(),
            page_keyword: $.trim($('#ui-import-page-keyword').val())
        }).done(function (res) {
            var $data = $(res.html);
            var $result = outlineFind($data, '#cms-import-result');
            if ($result.length && $result.attr('data-url')) {
                unsavedChanges = [];
                window.location.href = $result.attr('data-url');
                return;
            }
            var messages = [];
            outlineFind($data, '.alert-danger').each(function () {
                messages.push(outlineEsc($(this).text().replace(/\s+/g, ' ').replace(/^\s*×\s*/, '').trim()));
            });
            $error.html(messages.length ? messages.join('<br>') : 'The page was not imported.').removeClass('d-none');
        }).fail(function () {
            $error.text('The page was not imported.').removeClass('d-none');
        }).always(function () {
            $('#ui-import-page-submit').prop('disabled', false);
        });
    });
}

//******************************************* Quick search in the properties ***********************************************

/**
 * Add a search box to the properties header (next to the collapse button).
 * The user can find the content and property fields by their name,
 * description or type (e.g. `css`, `title`, `color`). Cards which contain
 * matching fields are expanded, the other fields and cards are hidden until
 * the search is cleared. When the properties panel is collapsed the box is
 * hidden (see cms_ui.css).
 */
function initFieldSearch() {
    var $form = $('.ui-card-properties form').first();
    var $header = $('#collapsePropertiesBtn > .d-flex').first();
    var $cards = $form.find('.ui-card-list');
    if ($form.length === 0 || $cards.length === 0 || $header.length === 0 || $header.find('.ui-field-search').length > 0) {
        return;
    }
    var $box = $('<div class="ui-field-search">' +
        '<i class="fas fa-search ui-field-search-icon"></i>' +
        '<input type="search" class="form-control form-control-sm ui-field-search-input" placeholder="Search content and properties" aria-label="Search content and properties" autocomplete="off">' +
        '<div class="ui-field-search-info small text-muted"></div>' +
        '</div>');
    $header.append($box);
    // clicks in the search must not collapse the properties panel
    $box.on('click mousedown', function (e) {
        e.stopPropagation();
    });
    var $input = $box.find('input');
    $input.on('input', function () {
        applyFieldSearch($cards, $box, $(this).val());
    }).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $(this).val('');
            applyFieldSearch($cards, $box, '');
        } else if (e.key === 'Enter') {
            e.preventDefault(); // enter must not submit the form
        }
    });
}

/**
 * The text of a field of the properties which is used for the search.
 * @param {jQuery} $item the element of the field
 * @returns {String}
 */
function fieldSearchText($item) {
    var text = $item.data('searchText');
    if (text === undefined) {
        var parts = [$item.children('.d-flex').first().text()];
        $item.find('[data-content]').each(function () {
            parts.push($('<div>').html($(this).attr('data-content')).text()); // the description of the field
        });
        $item.find('[name^="fields["]').first().each(function () {
            var match = $(this).attr('name').match(/^fields\[([^\]]+)\]/);
            if (match) {
                parts.push(match[1]);
            }
        });
        text = parts.join(' ').replace(/\s+/g, ' ').toLowerCase();
        $item.data('searchText', text);
    }
    return text;
}

/**
 * Show only the fields which match the search.
 * @param {jQuery} $cards the cards with the fields
 * @param {jQuery} $box the search box
 * @param {String} query the search text, empty to show everything
 */
function applyFieldSearch($cards, $box, query) {
    var tokens = query.toLowerCase().split(/\s+/).filter(Boolean);
    var total = 0;
    $cards.each(function () {
        var $card = $(this);
        var visible = 0;
        $card.children('.card-body').children().each(function () {
            var $item = $(this);
            var show = tokens.length === 0 || tokens.every(function (token) {
                return fieldSearchText($item).indexOf(token) !== -1;
            });
            $item.toggleClass('d-none', !show);
            if (show) {
                visible++;
            }
        });
        total += visible;
        if (tokens.length > 0) {
            if ($card.data('searchPrev') === undefined) {
                $card.data('searchPrev', outlineCardExpanded($card)); // restore the state when the search is cleared
            }
            $card.toggleClass('d-none', visible === 0);
            outlineSetCardExpanded($card, visible > 0);
        } else {
            if ($card.data('searchPrev') !== undefined) {
                outlineSetCardExpanded($card, $card.data('searchPrev'));
                $card.removeData('searchPrev');
            }
            $card.removeClass('d-none');
        }
    });
    var info = '';
    if (tokens.length > 0) {
        info = total === 0 ? 'No field matches' : (total === 1 ? '1 field matches' : total + ' fields match');
    }
    $box.find('.ui-field-search-info').text(info);
    if (typeof adjustPropertiesHeight === 'function') {
        adjustPropertiesHeight();
    }
}
