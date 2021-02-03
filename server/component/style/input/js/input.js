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
            dateFormat: $(this).attr('data-format') == '' ? 'Y-m-d H:i' : $(this).attr('data-format'),
            time_24hr: true,
            weekNumbers: false,
            locale: {
                firstDayOfWeek: 1
            },
            allowInput: true
        });
    });
}

function init_date_inputs() {
    $("input[type='date']").each(function () {
        $(this).flatpickr({
            enableTime: false,
            dateFormat: $(this).attr('data-format') == '' ? 'Y-m-d' : $(this).attr('data-format'),
            weekNumbers: false,
            locale: {
                firstDayOfWeek: 1
            },
            allowInput: true
        });
    });
}

function init_time_inputs() {
    $("input[type='time']").each(function () {
        $(this).flatpickr({
            enableTime: true,
            dateFormat: $(this).attr('data-format') == '' ? 'H:i' : $(this).attr('data-format'),
            time_24hr: true,
            allowInput: true,
            noCalendar: true,
        });
    });
}

function set_focus_btn_clcik() {
    $('.selfhelp-icon-btn').on("click", function () {
        $($(this).parent().parent().find('.selfhelp-input-date')[0]).focus();
    })
}