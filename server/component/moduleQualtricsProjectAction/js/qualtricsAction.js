function isHidden(el) {
    return (el.offsetParent === null)
}

function setRequiredIfDisplayed(elements) {
    for (let i = 0; i < elements.length; i++) {
        const el = elements[i];
        $(el).attr('required', !isHidden(el));
    }
}

function adjustRequiredFields() {
    setRequiredIfDisplayed($('select[name="schedule_info[id_qualtricsActionScheduleTypes]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[custom_time]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[delay_value_at_time]"]'));
    setRequiredIfDisplayed($('select[name="id_qualtricsSurveys_reminder"]'));
    setRequiredIfDisplayed($('select[name="schedule_info[notificationTypes]"]'));
    setRequiredIfDisplayed($('select[name="schedule_info[qualtricScheduleTypes]"]'));
    setRequiredIfDisplayed($('select[name="schedule_info[delay_value]"]'));
    setRequiredIfDisplayed($('select[name="schedule_info[delay_value_type]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[recipient]"]'));
    setRequiredIfDisplayed($('input[name="schedule_info[subject]"]'));
}

function adjustActionScheduleType() {
    $('#section-schedule_info').addClass('d-none');
    $('.style-section-id_qualtricsSurveys_reminder').addClass('d-none');
    if ($('select[name="id_qualtricsActionScheduleTypes"] option:selected').text().includes('Notification') ||
        $('select[name="id_qualtricsActionScheduleTypes"] option:selected').text().includes('Reminder')) {
        $('#section-schedule_info').removeClass('d-none');
    }
    if ($('select[name="id_qualtricsActionScheduleTypes"] option:selected').text().includes('Reminder')) {
        $('.style-section-id_qualtricsSurveys_reminder').removeClass('d-none');
    }
    adjustRequiredFields();
}

function adjustScheduleType() {
    $('#custom_time_holder').addClass('d-none');
    $('.send_after').addClass('d-none');
    $('.style-section-send_after_type').addClass('d-none');
    $('.style-section-send_on').addClass('d-none');
    $('.style-section-send_on_day').addClass('d-none');
    $('#at_time_holder').addClass('d-none');
    if ($('select[name="schedule_info[qualtricScheduleTypes]"] option:selected').text().includes('fixed datetime')) {
        $('#custom_time_holder').removeClass('d-none');
    } else if ($('select[name="schedule_info[qualtricScheduleTypes]"] option:selected').text().includes('time period on a weekday')) {
        $('.style-section-send_on').removeClass('d-none');
        $('.style-section-send_on_day').removeClass('d-none');
        $('#at_time_holder').removeClass('d-none');
    } else if ($('select[name="schedule_info[qualtricScheduleTypes]"] option:selected').text().includes('time period')) {
        $('.send_after').removeClass('d-none');
        $('.style-section-send_after_type').removeClass('d-none');
    }
    adjustRequiredFields();
}

$(document).ready(function () {
    adjustActionScheduleType();
    adjustScheduleType();
    adjustRequiredFields();
    $('select').selectpicker();
    if ($('textarea[name="schedule_info[body]"]')[0]) {
        var simplemde = new SimpleMDE({
            autoDownloadFontAwesome: false,
            spellChecker: false
        });
        if ($('.style-section-body code').length > 0) {
            $('.style-section-body code').first().html(simplemde.options.previewRender($('.style-section-body code').first().html()));
        }
    }

    // datepicker ***********************************************************************************
    $('#custom_time').flatpickr({
        enableTime: true,
        dateFormat: 'd-m-Y H:i',
        time_24hr: true,
        weekNumbers: true,
        minDate: "today",
        allowInput: true
    });


    $('#btncustom_time').on("click", function (e) {
        $('#custom_time').focus();
    })

    // at time ***********************************************************************************
    $('#send_on_day_at').flatpickr({
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        allowInput: true
    });


    $('#btnsend_on_day_at').on("click", function (e) {
        $('#send_on_day_at').focus();
    })

    $('#section-composeEmailForm .btn-warning').first().on('click', function (e) {
        if (new Date() >= flatpickr.parseDate($('#custom_time').val(), 'd-m-Y H:i')) {
            e.stopPropagation();
            e.preventDefault();
            $.alert({
                title: 'Wrong date!',
                content: 'The selected time already passed!',
            });
        }
        if (!flatpickr.parseDate($('#custom_time').val())) {
            e.stopPropagation();
            e.preventDefault();
            $.alert({
                title: 'Missing date!',
                content: 'Please enter date',
            });
        }
    });

    //on action_schedule_type change ******************************************************************************
    $('select[name="id_qualtricsActionScheduleTypes"]').on('change', function () {
        adjustActionScheduleType();
    });

    //on when (schedule_type) change ************************************************************************************
    $('select[name="schedule_info[qualtricScheduleTypes]"]').on('change', function () {
        adjustScheduleType();
    });
});