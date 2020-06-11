$(document).ready(function () {
    $('select').selectpicker();
    if ($('textarea[name="notification[body]"]')[0]) {
        new SimpleMDE({
            element: $('textarea[name="notification[body]"]')[0],
            autoDownloadFontAwesome: false,
            spellChecker: false
        });
    }
    if ($('textarea[name="reminder[body]"]')[0]) {
        new SimpleMDE({
            element: $('textarea[name="reminder[body]"]')[0],
            autoDownloadFontAwesome: false,
            spellChecker: false
        });
    }
});