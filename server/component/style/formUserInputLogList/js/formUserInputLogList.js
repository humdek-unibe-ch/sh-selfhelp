$(document).ready(function () {
    initFormUserInputLogList();
});

function initFormUserInputLogList() {
    $('.formUserInputLogList').each(function () {
        var $root = $(this);
        if ($root.data('ful-initialized')) {
            return;
        }
        $root.data('ful-initialized', true);

        var config = {};
        try {
            config = JSON.parse($root.attr('data-config') || '{}');
        } catch (e) {
            config = {};
        }

        initFormUserInputLogListTable($root);
        initFormUserInputLogListDelete($root, config);
        initFormUserInputLogListQuickActions($root, config);
        initFormUserInputLogListModal($root, config);
        initFormUserInputLogListDeepLinkFocus($root, config);
        initFormUserInputLogListAjax($root, config);
    });
}

/**
 * On ?record=&sid= deep-links, scroll this list/form into view so the user
 * is not left mid-page looking for the form (modal or page mode).
 */
function scrollFormUserInputLogListIntoView($root) {
    if (!$root || !$root.length || !$root[0] || typeof $root[0].scrollIntoView !== 'function') {
        return;
    }
    try {
        $root[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (e) {
        $root[0].scrollIntoView(true);
    }
}

function hasFormUserInputLogListDeepLink(config) {
    return config.urlRecordRaw !== null
        && config.urlRecordRaw !== undefined
        && String(config.urlRecordRaw) !== '';
}

/**
 * After save/cancel: drop ?record=&sid= and land on the list section.
 * Always resolves the live DOM root — never scroll a detached jQuery node
 * left over after soft-refresh replaceWith.
 */
function returnToFormUserInputLogListView(config) {
    if (config.listUrl && window.history && window.history.replaceState) {
        window.history.replaceState({}, '', config.listUrl);
    }
    window.setTimeout(function () {
        scrollFormUserInputLogListIntoView(findLiveFormUserInputLogListRoot(config));
    }, 50);
}

function findLiveFormUserInputLogListRoot(config) {
    var sectionClass = 'style-section-' + config.sectionId;
    var $live = $('.' + sectionClass + '.formUserInputLogList').first();
    if ($live.length === 0) {
        $live = $('.formUserInputLogList.' + sectionClass).first();
    }
    return $live;
}

/**
 * Tear down modal listeners before soft-refresh closes/replaces the root.
 * Prevents hidden.bs.modal from racing with DOM replacement.
 */
function unbindFormUserInputLogListModalHandlers(config) {
    if (!config.modalId) {
        return $();
    }
    var $modal = $('#' + config.modalId);
    if ($modal.length) {
        $modal.off('.ful');
        $modal.find('.form-cancel-btn').off('.ful');
    }
    return $modal;
}

function initFormUserInputLogListDeepLinkFocus($root, config) {
    if (!hasFormUserInputLogListDeepLink(config)) {
        return;
    }
    // Defer until after layout/modals so scroll targets the visible block.
    window.setTimeout(function () {
        scrollFormUserInputLogListIntoView($root);
    }, 50);
}

function initFormUserInputLogListTable($root) {
    $root.find('.formUserInputLogList-table').each(function () {
        var $table = $(this);
        if ($table.hasClass('dataTable') || typeof $.fn.DataTable !== 'function') {
            return;
        }
        var classes = ($table.attr('class') || '').split(/\s+/);
        var dataTableConfig = { columnDefs: [] };
        dataTableConfig.columnDefs.push({
            orderable: classes.indexOf('dt-sortable') !== -1,
            targets: '_all'
        });
        dataTableConfig.searching = classes.indexOf('dt-searching') !== -1;
        dataTableConfig.bPaginate = classes.indexOf('dt-bPaginate') !== -1;
        dataTableConfig.bInfo = classes.indexOf('dt-bInfo') !== -1;
        dataTableConfig.scrollX = true;

        var ordered = classes.filter(function (str) {
            return str.indexOf('dt-order') === 0;
        });
        for (var i = 0; i < ordered.length; i++) {
            var parts = ordered[i].split('-');
            if (parts.length === 4 && (parts[3] === 'asc' || parts[3] === 'desc')) {
                if (!dataTableConfig.order) {
                    dataTableConfig.order = [];
                }
                dataTableConfig.order.push([parseInt(parts[2], 10), parts[3]]);
            }
        }

        var hidden = classes.filter(function (str) {
            return str.indexOf('dt-hide') === 0;
        });
        for (var h = 0; h < hidden.length; h++) {
            var hParts = hidden[h].split('-');
            if (hParts.length === 3) {
                dataTableConfig.columnDefs.push({
                    targets: [parseInt(hParts[2], 10)],
                    visible: false
                });
            }
        }

        $table.DataTable(dataTableConfig);
    });
}

function initFormUserInputLogListDelete($root, config) {
    // Scope to this root only — multiple LogList styles on one page stay isolated.
    $root.find('.formUserInputLogList-btn-delete').off('click.ful').on('click.ful', function (e) {
        e.preventDefault();
        var recordId = parseInt($(this).data('record-id'), 10);
        var $input = $root.find('#' + config.deleteModalId).find('input[name="delete_record_id"]');
        if ($input.length === 0) {
            $input = $root.find('input[name="delete_record_id"]');
        }
        $input.val(recordId);
    });
}

/**
 * Configurable row quick actions: optional confirm, then POST via the hidden
 * quick form (AJAX soft-refresh or full reload depending on style ajax).
 * Delegated from the root so rows DataTables detaches for pagination still work.
 */
function initFormUserInputLogListQuickActions($root, config) {
    if (!config.hasQuickActions) {
        return;
    }
    $root.off('click.fulQuick', '.formUserInputLogList-btn-quick').on('click.fulQuick', '.formUserInputLogList-btn-quick', function (e) {
        e.preventDefault();
        if ($root.data('ful-quick-busy')) {
            return;
        }
        var $btn = $(this);
        var actionId = String($btn.attr('data-action-id') || '');
        var recordId = parseInt($btn.attr('data-record-id'), 10);
        if (!actionId || !(recordId > 0)) {
            return;
        }
        var defaultMsg = config.quickConfirmDefault || 'Do you want to continue?';
        var okLabel = config.quickConfirmOk || 'OK';
        var cancelLabel = config.quickConfirmCancel || 'Cancel';
        var needsConfirm = String($btn.attr('data-confirm') || '') === '1';
        var run = function () {
            submitFormUserInputLogListQuickAction($root, config, actionId, recordId);
        };
        if (needsConfirm) {
            if (typeof $.confirm !== 'function') {
                if (window.confirm($btn.attr('data-confirm-message') || defaultMsg)) {
                    run();
                }
                return;
            }
            $.confirm({
                type: 'blue',
                title: $btn.attr('data-confirm-title') || '',
                content: $btn.attr('data-confirm-message') || defaultMsg,
                buttons: {
                    confirm: {
                        text: okLabel,
                        action: run
                    },
                    cancel: {
                        text: cancelLabel,
                        action: function () { }
                    }
                }
            });
            return;
        }
        run();
    });
}

function setFormUserInputLogListQuickBusy($root, busy) {
    $root.data('ful-quick-busy', !!busy);
    $root.find('.formUserInputLogList-btn-quick').toggleClass('ful-quick-busy', !!busy);
    if (busy) {
        $root.find('.formUserInputLogList-btn-quick').attr('aria-disabled', 'true');
    } else {
        $root.find('.formUserInputLogList-btn-quick').removeAttr('aria-disabled');
    }
}

function submitFormUserInputLogListQuickAction($root, config, actionId, recordId) {
    if ($root.data('ful-quick-busy')) {
        return;
    }
    var $form = $root.find('.formUserInputLogList-quick-form').first();
    if ($form.length === 0) {
        return;
    }
    setFormUserInputLogListQuickBusy($root, true);
    $form.find('input[name="quick_action_id"]').val(actionId);
    $form.find('input[name="selected_record_id"]').val(String(recordId));
    if (config.listUrl) {
        $form.attr('action', String(config.listUrl).split('#')[0]);
    }
    // Trigger the same fulAjax / native submit path as delete and edit forms.
    $form.trigger('submit');
}

function initFormUserInputLogListModal($root, config) {
    if (!config.showFormInModal) {
        return;
    }
    var $modal = $root.find('#' + config.modalId);
    if ($modal.length === 0) {
        $modal = $('#' + config.modalId);
    }
    if ($modal.length === 0) {
        return;
    }

    // Namespaced .ful so soft-refresh can unbind without touching Bootstrap internals.
    $root.find('.formUserInputLogList-btn-new').off('click.ful').on('click.ful', function () {
        prepareFormUserInputLogListFormForCreate($root);
        $modal.find('.formUserInputLogList-modal-title').text(config.labelNew || 'New');
    });

    // Form cancel in modal dismisses instead of navigating (url is "#").
    $modal.find('.form-cancel-btn').off('click.ful').on('click.ful', function (e) {
        e.preventDefault();
        $modal.modal('hide');
    });

    $modal.off('shown.bs.modal.ful').on('shown.bs.modal.ful', function () {
        // Defer past other document.ready handlers (select.js) and layout so
        // edit deep-link open gets the same working picker as a late "New" click.
        window.setTimeout(function () {
            refreshFormUserInputLogListModalWidgets($modal);
        }, 0);
        if (hasFormUserInputLogListDeepLink(config)) {
            scrollFormUserInputLogListIntoView($root);
        }
    });

    // User dismiss (Cancel / X / backdrop): clean URL + scroll to live list.
    // Soft-refresh unbinds this handler before hide so it does not race replaceWith.
    $modal.off('hidden.bs.modal.ful').on('hidden.bs.modal.ful', function () {
        returnToFormUserInputLogListView(config);
    });

    if (hasFormUserInputLogListDeepLink(config)) {
        scrollFormUserInputLogListIntoView($root);
        // Clear/title before show so shown.bs.modal refresh sees the create state.
        if (parseInt(config.urlRecordId, 10) > 0) {
            $modal.find('.formUserInputLogList-modal-title').text(config.labelEdit || 'Edit');
        } else {
            $modal.find('.formUserInputLogList-modal-title').text(config.labelNew || 'New');
            prepareFormUserInputLogListFormForCreate($root);
        }
        // Defer show: opening during the same ready turn as select.js leaves
        // edit-mode pickers without a body-attached menu (New click is later).
        window.setTimeout(function () {
            $modal.modal('show');
        }, 0);
    }
}

/**
 * Re-init / refresh form widgets that were built while the modal was hidden.
 * - bootstrap-select (Status, etc.): destroy + re-init with container:body so the
 *   dropdown menu is not clipped by .modal-body overflow / modal-content
 * - image_selector IconSelect: rebuild from data-values once visible
 */
function refreshFormUserInputLogListModalWidgets($modal) {
    if (!$modal || !$modal.length) {
        return;
    }

    $modal.find('select.bootstrapSelect').each(function () {
        ensureFormUserInputLogListModalSelectpicker($(this));
    });

    // Root only (.selectImage). Do not use [id^="selectImage-"] — IconSelect
    // also creates #selectImage-N-box-scroll; re-initing that child breaks it.
    $modal.find('.selectImage').each(function () {
        if (typeof initSelectImage !== 'function') {
            return;
        }
        $(this).off('changed');
        initSelectImage(this);
    });
}

/**
 * bootstrap-select menus are position:absolute inside .modal-body. With
 * overflow-y:auto (and page CSS overflow:hidden on .modal-content) the open
 * list is clipped — especially for the last field (Status). Attach the menu
 * to document.body instead.
 *
 * @param {jQuery} $select
 */
function ensureFormUserInputLogListModalSelectpicker($select) {
    if (!$select || !$select.length || typeof $select.selectpicker !== 'function') {
        return;
    }
    var options = {
        showTick: true,
        allowClear: true,
        container: 'body'
    };
    var sizeAttr = $select.attr('data-size');
    if (sizeAttr !== undefined && sizeAttr !== null && sizeAttr !== '') {
        var parsedSize = parseInt(sizeAttr, 10);
        options.size = isNaN(parsedSize) ? sizeAttr : parsedSize;
    }
    var picker = $select.data('selectpicker');
    // Already correct (e.g. second shown refresh) — only refresh visuals.
    if (picker && picker.options && picker.options.container === 'body') {
        $select.selectpicker('refresh');
        return;
    }
    if (picker) {
        $select.selectpicker('destroy');
    }
    $select.selectpicker(options);
}

/**
 * Prepare the modal/page form for a new insert.
 * Do not use form.reset() after an edit render — reset restores the
 * server-rendered edit defaults. Clear user fields and update defaults so
 * a later reset also stays empty.
 */
function prepareFormUserInputLogListFormForCreate($root) {
    var $form = $root.find('.formUserInputLogList-modal form').first();
    if ($form.length === 0) {
        $form = $root.find('.formUserInputLogList-form form').first();
    }
    if ($form.length === 0) {
        return;
    }
    $form.find('input[name="selected_record_id"]').remove();
    clearFormUserInputLogListUserFields($form);
}

var FORM_USER_INPUT_LOG_LIST_SYSTEM_FIELDS = {
    ajax: true,
    is_log: true,
    __form_name: true,
    redirect_at_end: true,
    delete_record_id: true,
    selected_record_id: true,
    record_id: true
};

function clearFormUserInputLogListUserFields($form) {
    $form.find('input, select, textarea').each(function () {
        var el = this;
        var name = el.name || '';
        if (!name) {
            return;
        }
        var baseName = name.split('[')[0];
        if (FORM_USER_INPUT_LOG_LIST_SYSTEM_FIELDS[baseName] || FORM_USER_INPUT_LOG_LIST_SYSTEM_FIELDS[name]) {
            return;
        }
        // Keep section/field id companions used by FormUserInputController/GUMP.
        if (name.indexOf('[id]') !== -1) {
            return;
        }

        var tag = (el.tagName || '').toLowerCase();
        var type = (el.type || '').toLowerCase();

        if (type === 'file') {
            el.value = '';
            return;
        }
        if (type === 'checkbox' || type === 'radio') {
            el.checked = false;
            el.defaultChecked = false;
            return;
        }
        if (tag === 'select') {
            $(el).find('option').each(function () {
                this.selected = false;
                this.defaultSelected = false;
            });
            el.value = '';
            el.selectedIndex = -1;
            var $empty = $(el).find('option[value=""]').first();
            if ($empty.length) {
                $empty.prop('selected', true);
                $empty[0].defaultSelected = true;
                el.value = '';
            }
            // Visual refresh happens on modal shown (refreshFormUserInputLogListModalWidgets).
            if ($(el).data('selectpicker') && typeof $(el).selectpicker === 'function') {
                $(el).selectpicker('refresh');
            }
            return;
        }

        el.value = '';
        el.defaultValue = '';
    });
}

/**
 * AJAX + optional jquery-confirm for forms inside this style only.
 * Replaces form.js submit handlers on these forms so confirm does not call
 * native form.submit() (which would bypass AJAX).
 */
function initFormUserInputLogListAjax($root, config) {
    $root.find('form').each(function () {
        var form = this;
        var $form = $(form);
        // Replace any prior submit handlers on THIS form only (scoped root).
        $form.off('submit').on('submit.fulAjax', function (e) {
            var isAjax = String($form.find('input[name="ajax"]').val()) === '1';
            var confirmation = $form.data('confirmation');
            var needsConfirm = confirmation && confirmation.confirmation_title;

            if (!isAjax) {
                if (!needsConfirm) {
                    return;
                }
                e.preventDefault();
                e.stopImmediatePropagation();
                $.confirm({
                    type: 'red',
                    title: confirmation.confirmation_title,
                    content: confirmation.confirmation_message,
                    buttons: {
                        confirm: {
                            text: confirmation.confirmation_continue,
                            action: function () {
                                HTMLFormElement.prototype.submit.call(form);
                            }
                        },
                        cancel: {
                            text: confirmation.confirmation_cancel,
                            action: function () { }
                        }
                    }
                });
                return;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            var runAjax = function () {
                submitFormUserInputLogListAjax($root, $form, config);
            };

            if (needsConfirm) {
                $.confirm({
                    type: 'red',
                    title: confirmation.confirmation_title,
                    content: confirmation.confirmation_message,
                    buttons: {
                        confirm: {
                            text: confirmation.confirmation_continue,
                            action: runAjax
                        },
                        cancel: {
                            text: confirmation.confirmation_cancel,
                            action: function () { }
                        }
                    }
                });
                return;
            }
            runAjax();
        });
    });
}

function submitFormUserInputLogListAjax($root, $form, config) {
    $('.alert-danger').remove();
    $('.alert-success').remove();

    var btnLabels = [];
    $form.find(':submit').each(function () {
        var origLabel = $(this).html();
        btnLabels.push({ btn: this, origLabel: origLabel });
        $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span> ' + origLabel);
    });

    var redirectAtEnd = ($form.find('input[name="redirect_at_end"]').val() || '').trim();
    // Post to the list URL so the response contains the list (not page-form-only HTML
    // when the browser is on ?record=…&sid=…).
    var postUrl = (config.listUrl || $form.attr('action') || window.location.pathname).split('#')[0];

    $.ajax({
        type: 'post',
        url: postUrl,
        data: $form.serialize(),
        success: function (data) {
            var parser = new DOMParser();
            var htmlDoc = parser.parseFromString(data, 'text/html');
            var $newRoot = findFormUserInputLogListRoot(htmlDoc, config);
            var writeResult = $newRoot.length ? String($newRoot.attr('data-write-result') || '') : '';
            var hasSuccess = writeResult === 'success'
                || $(htmlDoc).find('.alert-success').length > 0;
            var hasFail = writeResult === 'fail'
                || ($(htmlDoc).find('.alert-danger').length > 0 && !hasSuccess);

            btnLabels.forEach(function (element) {
                $(element.btn).html(element.origLabel);
            });

            var isQuick = $form.hasClass('formUserInputLogList-quick-form');

            if (hasFail) {
                // Quick actions: soft-refresh so row buttons match current when
                // (stale clicks). Alerts come from the replaced root markup.
                if (isQuick && $newRoot.length) {
                    softRefreshFormUserInputLogList($root, htmlDoc, config, {
                        preserveView: true
                    });
                    return;
                }
                if (isQuick) {
                    setFormUserInputLogListQuickBusy($root, false);
                }
                placeFormUserInputLogListAjaxAlerts(
                    $root,
                    $form,
                    $(htmlDoc).find('.alert-danger')
                );
                return;
            }

            if (redirectAtEnd) {
                window.location = redirectAtEnd;
                return;
            }

            if (hasSuccess) {
                softRefreshFormUserInputLogList($root, htmlDoc, config, {
                    preserveView: isQuick
                });
                return;
            }

            // No explicit success/fail marker (e.g. empty alert_success): still return
            // to the list after a processed write with no danger alerts.
            if ($(htmlDoc).find('.alert-danger').length === 0) {
                softRefreshFormUserInputLogList($root, htmlDoc, config, {
                    preserveView: isQuick
                });
                return;
            }

            if (isQuick) {
                setFormUserInputLogListQuickBusy($root, false);
            }
            placeFormUserInputLogListAjaxAlerts(
                $root,
                $form,
                $(htmlDoc).find('.alert-danger, .alert-success')
            );
        },
        error: function () {
            btnLabels.forEach(function (element) {
                $(element.btn).html(element.origLabel);
            });
            if ($form.hasClass('formUserInputLogList-quick-form')) {
                setFormUserInputLogListQuickBusy($root, false);
            }
        }
    });
}

/**
 * Insert AJAX alerts where they are visible. Quick-action posts use a hidden
 * form at the end of the root — do not insertBefore that form.
 */
function placeFormUserInputLogListAjaxAlerts($root, $form, $alerts) {
    if (!$alerts || !$alerts.length) {
        return;
    }
    var useRoot = $form.hasClass('formUserInputLogList-quick-form');
    if (useRoot) {
        var $anchor = $root.find(
            '.formUserInputLogList-list, .formUserInputLogList-table, .formUserInputLogList-cards, .formUserInputLogList-empty, .formUserInputLogList-toolbar'
        ).first();
        // Prepend in document order (reverse walk so first alert stays on top).
        $($alerts.get().reverse()).each(function () {
            if ($anchor.length) {
                $(this).insertBefore($anchor[0]);
            } else {
                $root.prepend(this);
            }
        });
        return;
    }
    $alerts.each(function () {
        $(this).insertBefore($form[0]);
    });
}

function findFormUserInputLogListRoot(htmlDoc, config) {
    var sectionClass = 'style-section-' + config.sectionId;
    var $newRoot = $(htmlDoc).find('.' + sectionClass + '.formUserInputLogList').first();
    if ($newRoot.length === 0) {
        $newRoot = $(htmlDoc).find('.formUserInputLogList.' + sectionClass).first();
    }
    return $newRoot;
}

function closeFormUserInputLogListModals(config) {
    if (config.modalId) {
        $('#' + config.modalId).modal('hide');
    }
    if (config.deleteModalId) {
        $('#' + config.deleteModalId).modal('hide');
    }
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
}

/**
 * Capture DataTables page/order/search so quick-action soft-refresh can restore them.
 *
 * @returns {Array<{page:number,order:*,search:string}>}
 */
function captureFormUserInputLogListTableState($root) {
    var saved = [];
    if (!$.fn.DataTable) {
        return saved;
    }
    $root.find('.formUserInputLogList-table').each(function () {
        if (!$.fn.DataTable.isDataTable(this)) {
            saved.push(null);
            return;
        }
        var api = $(this).DataTable();
        saved.push({
            page: api.page(),
            order: api.order(),
            search: api.search()
        });
    });
    return saved;
}

function restoreFormUserInputLogListTableState($root, saved) {
    if (!saved || !saved.length || !$.fn.DataTable) {
        return;
    }
    $root.find('.formUserInputLogList-table').each(function (idx) {
        if (!saved[idx] || !$.fn.DataTable.isDataTable(this)) {
            return;
        }
        var api = $(this).DataTable();
        var state = saved[idx];
        if (typeof state.search === 'string') {
            api.search(state.search);
        }
        if (state.order) {
            api.order(state.order);
        }
        if (typeof state.page === 'number') {
            api.page(state.page);
        }
        api.draw(false);
    });
}

/**
 * After a successful write with no redirect_at_end: close modals and show the list.
 * Prefers soft-replacing the component from the AJAX HTML; falls back to a full
 * navigation to listUrl so the list is always visible.
 *
 * @param {object} [options]
 * @param {boolean} [options.preserveView] Keep scroll + DataTables state (quick actions).
 */
function softRefreshFormUserInputLogList($root, htmlDoc, config, options) {
    options = options || {};
    var preserveView = !!options.preserveView;
    var savedTables = preserveView ? captureFormUserInputLogListTableState($root) : [];
    var scrollY = preserveView
        ? (window.scrollY || window.pageYOffset || 0)
        : 0;

    // Unbind first so modal('hide') cannot fire return-to-list against a root
    // that we are about to detach/replace.
    unbindFormUserInputLogListModalHandlers(config);
    closeFormUserInputLogListModals(config);

    var $newRoot = findFormUserInputLogListRoot(htmlDoc, config);
    var listUrl = config.listUrl || window.location.pathname;

    if ($newRoot.length === 0) {
        window.location = listUrl;
        return;
    }

    // Page-form responses still include the form shell without a usable list —
    // navigate hard so the user always lands on the list (#section-… included).
    if ($newRoot.find('.formUserInputLogList-list').length === 0
        && $newRoot.find('.formUserInputLogList-table').length === 0) {
        window.location = listUrl;
        return;
    }

    if ($root[0]) {
        $root[0].dispatchEvent(new CustomEvent('selfhelp:loglist:before-replace', {
            bubbles: true,
            detail: { root: $root[0], sectionId: config.sectionId }
        }));
    }
    $root.replaceWith($newRoot);
    // Re-bind this style (and leave other page forms to their own handlers).
    initFormUserInputLogList();
    if ($newRoot[0]) {
        $newRoot[0].dispatchEvent(new CustomEvent('selfhelp:loglist:after-replace', {
            bubbles: true,
            detail: { root: $newRoot[0], sectionId: config.sectionId }
        }));
    }

    if (preserveView) {
        var $live = findLiveFormUserInputLogListRoot(config);
        restoreFormUserInputLogListTableState($live, savedTables);
        window.scrollTo(0, scrollY);
        return;
    }
    // Single return path after create/edit/delete (live root only).
    returnToFormUserInputLogListView(config);
}
