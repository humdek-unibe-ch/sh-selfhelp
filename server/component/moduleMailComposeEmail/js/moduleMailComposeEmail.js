$(document).ready(function () {
    $('recipients').selectpicker();

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
        minDate: "today",
        allowInput: true
    });

    $('#btntime_to_be_sent').on("click", function (e) {
        $('#time_to_be_sent').focus();
    })

    $('#section-composeEmailForm .btn-warning').first().on('click', function (e) {
        if (new Date() >= flatpickr.parseDate($('#time_to_be_sent').val(), 'd-m-Y H:i')) {
            e.stopPropagation();
            e.preventDefault();
            $.alert({
                title: 'Wrong date!',
                content: 'The selected time already passed!',
            });
        }
        if (!flatpickr.parseDate($('#time_to_be_sent').val())) {
            e.stopPropagation();
            e.preventDefault();
            $.alert({
                title: 'Missing date!',
                content: 'Please enter date',
            });
        }
    });
});