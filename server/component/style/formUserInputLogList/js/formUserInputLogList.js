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
        initFormUserInputLogListModal($root, config);
        initFormUserInputLogListAjax($root, config);
    });
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

    $root.find('.formUserInputLogList-btn-new').off('click.ful-new').on('click.ful-new', function () {
        prepareFormUserInputLogListFormForCreate($root);
        $modal.find('.formUserInputLogList-modal-title').text(config.labelNew || 'New');
    });

    if (config.urlRecordRaw !== null && config.urlRecordRaw !== undefined && String(config.urlRecordRaw) !== '') {
        $modal.modal('show');
        if (parseInt(config.urlRecordId, 10) > 0) {
            $modal.find('.formUserInputLogList-modal-title').text(config.labelEdit || 'Edit');
        } else {
            $modal.find('.formUserInputLogList-modal-title').text(config.labelNew || 'New');
            prepareFormUserInputLogListFormForCreate($root);
        }
    }

    $modal.on('hidden.bs.modal', function () {
        if (config.urlRecordRaw && config.listUrl) {
            var current = window.location.pathname + (window.location.search || '');
            if (current !== config.listUrl) {
                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, '', config.listUrl);
                }
            }
        }
    });
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

            if (hasFail) {
                $(htmlDoc).find('.alert-danger').each(function () {
                    $(this).insertBefore($form[0]);
                });
                return;
            }

            if (redirectAtEnd) {
                window.location = redirectAtEnd;
                return;
            }

            if (hasSuccess) {
                softRefreshFormUserInputLogList($root, htmlDoc, config);
                return;
            }

            // No explicit success/fail marker (e.g. empty alert_success): still return
            // to the list after a processed write with no danger alerts.
            if ($(htmlDoc).find('.alert-danger').length === 0) {
                softRefreshFormUserInputLogList($root, htmlDoc, config);
                return;
            }

            $(htmlDoc).find('.alert-danger, .alert-success').each(function () {
                $(this).insertBefore($form[0]);
            });
        },
        error: function () {
            btnLabels.forEach(function (element) {
                $(element.btn).html(element.origLabel);
            });
        }
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
 * After a successful write with no redirect_at_end: close modals and show the list.
 * Prefers soft-replacing the component from the AJAX HTML; falls back to a full
 * navigation to listUrl so the list is always visible.
 */
function softRefreshFormUserInputLogList($root, htmlDoc, config) {
    closeFormUserInputLogListModals(config);

    var $newRoot = findFormUserInputLogListRoot(htmlDoc, config);
    var listUrl = config.listUrl || window.location.pathname;

    if ($newRoot.length === 0) {
        window.location = listUrl;
        return;
    }

    // Page-form responses still include the form shell without a usable list —
    // navigate hard so the user always lands on the list.
    if ($newRoot.find('.formUserInputLogList-list').length === 0
        && $newRoot.find('.formUserInputLogList-table').length === 0) {
        window.location = listUrl;
        return;
    }

    $root.replaceWith($newRoot);
    if (listUrl && window.history && window.history.replaceState) {
        window.history.replaceState({}, '', listUrl);
    }
    // Re-bind this style (and leave other page forms to their own handlers).
    initFormUserInputLogList();
}
