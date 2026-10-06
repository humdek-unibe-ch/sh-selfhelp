/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/**
 * Language picker.
 *
 * Sets the session language through the same endpoint the footer picker uses,
 * then either reloads or moves on to the page named by `redirect_at_select`.
 * The reply is waited for so the language is in the session before the next
 * page renders; a failed call still navigates rather than stranding the user.
 */
$(document).ready(function () {
    $('.selfHelp-language-picker').each(function () {
        var $picker = $(this);
        var redirect = $picker.data('redirect');

        function setLanguage(id) {
            if (!id) return;
            $picker.find('button, select').prop('disabled', true);
            $.post(
                BASE_PATH + '/request/AjaxLanguage/ajax_set_user_language',
                { id_languages: id },
                null,
                'json'
            ).always(function () {
                if (redirect) {
                    window.location.href = redirect;
                } else {
                    window.location.reload();
                }
            });
        }

        $picker.on('click', '.selfHelp-language-picker__button', function () {
            setLanguage($(this).data('language'));
        });

        $picker.on('change', '.selfHelp-language-picker__select', function () {
            setLanguage($(this).val());
        });
    });
});
