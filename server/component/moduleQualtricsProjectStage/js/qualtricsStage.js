$(document).ready(function () {
    $('select').selectpicker();
    new SimpleMDE({
        element: $('textarea[name="notification[body]"]')[0],
        autoDownloadFontAwesome: false,
        spellChecker: false
    });
    new SimpleMDE({
        element: $('textarea[name="reminder[body]"]')[0],
        autoDownloadFontAwesome: false,
        spellChecker: false
    });
});