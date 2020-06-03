$(document).ready(function () {
    $('select').selectpicker();

    new SimpleMDE({
        element: $('textarea[name="body"]')[0],
        autoDownloadFontAwesome: false,
        spellChecker: false
    });

    $('#time_to_be_sent').flatpickr({
        enableTime: true,
        dateFormat: 'd-m-Y H:i',
        time_24hr: true,
        weekNumbers: true,
    });

    $('#btntime_to_be_sent').on("click", function (e) {
        $('#time_to_be_sent').focus();
    })
});