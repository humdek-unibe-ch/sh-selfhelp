$(document).ready(() => {
    flatpickr.l10ns.default.firstDayOfWeek = 1;
    flatpickr.l10ns.en.firstDayOfWeek = 1;
    init_datetime_inputs();
    init_date_inputs();
    init_time_inputs();
    check_input_locked_after_submit();
    set_focus_btn_clcik();
});

function check_input_locked_after_submit(){
    $('.selfhelpInput').each(function(){
        if($(this).data('locked_after_submit') && $(this).val()){
            $(this).prop('readonly', true);
            if (this._flatpickr) {
                // readonly does not stop the calendar from opening
                this._flatpickr.set('clickOpens', false);
                if (this._flatpickr.altInput) {
                    $(this._flatpickr.altInput).prop('readonly', true);
                }
            }
        }
    })
}

/**
 * The input keeps the stored value (`storageFormat`, always ISO) and is
 * submitted; when the display format (`data-format`) differs, flatpickr shows
 * a second input in that format (altInput) and hides the original one.
 */
function init_date_picker(input, storageFormat, options) {
    const displayFormat = $(input).attr('data-format') || storageFormat;
    $(input).flatpickr(Object.assign({
        dateFormat: storageFormat,
        altInput: displayFormat !== storageFormat,
        altFormat: displayFormat,
        altInputClass: input.className,
        allowInput: true,
        locale: $(input).attr('data-locale')
    }, options));
}

function init_datetime_inputs() {
    $("input[type='datetime']").each(function () {
        init_date_picker(this, 'Y-m-d H:i', {
            enableTime: true,
            time_24hr: true,
            weekNumbers: false,
            defaultDate: $(this).attr('value') == 'now' ? new Date() : ''
        });
    });
}

function init_date_inputs() {
    $("input[type='date']").each(function () {
        init_date_picker(this, 'Y-m-d', {
            enableTime: false,
            weekNumbers: false,
            defaultDate: $(this).attr('value') == 'now' ? new Date() : ''
        });
    });
}

function init_time_inputs() {
    $("input[type='time']").each(function () {
        init_date_picker(this, 'H:i', {
            enableTime: true,
            time_24hr: true,
            noCalendar: true
        });
    });
}

function set_focus_btn_clcik() {
    $('.selfhelp-icon-btn').on("click", function () {
        // the visible input: the alt input when the original one is hidden
        $($(this).parent().parent().find('.selfhelp-input-date').not('[type=hidden]')[0]).focus();
    })
}
