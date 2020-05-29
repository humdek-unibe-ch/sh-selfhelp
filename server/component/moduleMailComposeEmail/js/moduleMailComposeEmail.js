$(document).ready(function () {
    $('select').selectpicker();

    new SimpleMDE({
        element: $('textarea[name="body"]')[0],
        autoDownloadFontAwesome: false,
        spellChecker: false
    });

    $('#time_to_be_sent').datepicker({
        calendarWeeks: true,
        autoclose: true,
        todayHighlight: true,
        format: 'dd-mm-yyyy HH:MM',
    });
    $('#btntime_to_be_sent').on("click", function (e) {
        $('#time_to_be_sent').focus();
    })
});