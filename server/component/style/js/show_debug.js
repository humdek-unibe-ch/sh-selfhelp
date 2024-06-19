/**
 *This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. 
 */
$(document).ready(function () {
    $('.data-debug').each(function () {
        var debug_data = $(this).data('debug');
        if (debug_data) {
            console.log(debug_data['field']['section_name']);
            console.log(debug_data);
            $(this).removeData('debug').removeAttr('data-debug');
        }
    });
});
