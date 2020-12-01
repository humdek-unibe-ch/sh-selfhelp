$(document).ready(() => {
    init_datetime_inputs();
    init_date_inputs();
    init_time_inputs();
    set_focus_btn_clcik();
});

function init_datetime_inputs() {
    $("input[type='datetime']").each(function () {
        $(this).flatpickr({
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            time_24hr: true,
            weekNumbers: true,
            allowInput: true
        });
    });
}

function init_date_inputs() {
    $("input[type='date']").each(function () {
        $(this).flatpickr({
            enableTime: false,
            dateFormat: 'Y-m-d',
            weekNumbers: true,
            allowInput: true
        });
    });
}

function init_time_inputs() {
    $("input[type='time']").each(function () {
        $(this).flatpickr({
            enableTime: true,
            dateFormat: 'H:i',
            time_24hr: true,
            allowInput: true,
            noCalendar: true,
        });
    });
}

function set_focus_btn_clcik() {
    $('.selfhelp-icon-btn').on("click", function () {
        var section_id = $(this).attr('id').replace('selfhelp-icon-btn-', '');
        $('#selfhelp-input-'+section_id).focus();
    })
}