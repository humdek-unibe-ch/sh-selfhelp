/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/**
 * Opens the version history of a versioned CMS field in the modal
 * #cms-field-versions-modal (cmsFieldVersions/tpl_modal.php, rendered by the
 * CMS). The history is the page cmsFieldVersions, rendered on the server: the
 * modal shows its element `.cms-field-versions`, loads a selected version the
 * same way and posts a restore to the page's controller. After a restore the
 * modal closes and the CMS page reloads, so the field shows the restored
 * content.
 */
$(document).ready(function () {
    var $modal = $('#cms-field-versions-modal');
    var restored = false;
    if ($modal.length === 0) {
        return;
    }

    function showTemplate(name) {
        var $content = $($modal.find('.cms-fv-' + name + '-template').html());
        $modal.find('.modal-body').empty().append($content);
        return $content;
    }

    function showError(message) {
        showTemplate('error').text(message);
    }

    function show(html) {
        // Scripts of the page are not executed: only its markup is used.
        var $history = $('<div>').append($.parseHTML(html)).find('.cms-field-versions').first();
        if ($history.length === 0) {
            showError('The history of this field could not be loaded. You may not have the rights to change it.');
            return;
        }
        $modal.find('.cms-fv-title').text($history.data('title'));
        $modal.find('.cms-fv-subtitle').text($history.data('subtitle'));
        $modal.find('.modal-body').empty().append($history);
    }

    function load(url) {
        showTemplate('loading');
        $.get(url).done(show).fail(function () {
            showError('The history of this field could not be loaded.');
        });
    }

    function restore(form) {
        showTemplate('loading');
        $.post(form.action, $(form).serialize()).done(function () {
            restored = true;
            $modal.modal('hide');
        }).fail(function () {
            showError('The version could not be restored.');
        });
    }

    function confirmRestore(form) {
        var confirmation = $(form).data('confirmation') || {};
        $.confirm({
            type: 'orange',
            title: confirmation.confirmation_title,
            content: confirmation.confirmation_message,
            buttons: {
                confirm: {
                    text: confirmation.confirmation_continue,
                    btnClass: 'btn-warning',
                    action: function () {
                        restore(form);
                    }
                },
                cancel: {
                    text: confirmation.confirmation_cancel
                }
            }
        });
    }

    $modal.on('click', '.cms-fv-version', function (e) {
        e.preventDefault();
        load(this.href);
    });
    $modal.on('submit', '.cms-fv-restore', function (e) {
        e.preventDefault();
        confirmRestore(this);
    });
    $modal.on('hidden.bs.modal', function () {
        if (restored) {
            location.reload();
        }
    });
    $(document).on('click', '.field-versions-link', function (e) {
        e.preventDefault();
        $modal.modal('show');
        load(this.href);
    });
});
